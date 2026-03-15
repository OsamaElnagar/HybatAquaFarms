<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use App\Models\SalesOrder;
use DefStudio\Telegraph\Models\TelegraphChat;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class CloseBatch extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BatchResource::class;

    protected string $view = 'filament.resources.batches.pages.close-batch';

    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        if ($this->record->is_cycle_closed) {
            Notification::make()->warning()->title('هذه الدورة مغلقة مسبقاً')->send();
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));

            return;
        }

        $this->form->fill();
    }

    public function getTitle(): string
    {
        return 'إقفال الدورة: '.$this->record->batch_code;
    }

    public function form(Schema $schema): Schema
    {
        $baseFeedCost = (float) $this->record->total_feed_cost;
        $baseOperatingExpenses = (float) $this->record->allocated_expenses;
        $baseTotalCost = (float) $this->record->total_cost;
        $baseTotalRevenue = (float) SalesOrder::query()
            ->whereHas('harvestOperation', function ($q) {
                $q->where('batch_id', $this->record->id);
            })->sum('net_amount');

        return $schema
            ->components([
                Section::make('الملخص المالي للدورة')
                    ->columns(4)
                    ->schema([
                        Placeholder::make('total_cost')
                            ->label('تكلفة الزريعة')
                            ->content(Number::currency($baseTotalCost, 'EGP', precision: 0)),
                        Placeholder::make('total_feed_cost')
                            ->label('تكلفة الأعلاف')
                            ->content(Number::currency($baseFeedCost, 'EGP', precision: 0)),
                        Placeholder::make('allocated_expenses')
                            ->label('المصروفات التشغيلية الأساسية')
                            ->content(Number::currency($baseOperatingExpenses, 'EGP', precision: 0)),

                        Placeholder::make('total_cycle_expenses')
                            ->label('إجمالي التكاليف')
                            ->content(function (Get $get) use ($baseTotalCost, $baseFeedCost, $baseOperatingExpenses) {
                                $transactions = $get('misc_transactions') ?? [];
                                $miscExp = collect($transactions)->where('type', 'expense')->sum('amount');
                                $total = $baseTotalCost + $baseFeedCost + $baseOperatingExpenses + $miscExp;

                                return Number::currency($total, 'EGP', precision: 0);
                            }),

                        Placeholder::make('total_revenue')
                            ->label('إجمالي الإيرادات')
                            ->content(function (Get $get) use ($baseTotalRevenue) {
                                $transactions = $get('misc_transactions') ?? [];
                                $miscRev = collect($transactions)->where('type', 'revenue')->sum('amount');
                                $total = $baseTotalRevenue + $miscRev;

                                return Number::currency($total, 'EGP', precision: 0);
                            }),

                        Placeholder::make('net_profit')
                            ->label('صافي الربح / الخسارة')
                            ->content(function (Get $get) use ($baseTotalCost, $baseFeedCost, $baseOperatingExpenses, $baseTotalRevenue) {
                                $transactions = $get('misc_transactions') ?? [];
                                $miscExp = collect($transactions)->where('type', 'expense')->sum('amount');
                                $miscRev = collect($transactions)->where('type', 'revenue')->sum('amount');

                                $totalExp = $baseTotalCost + $baseFeedCost + $baseOperatingExpenses + $miscExp;
                                $totalRev = $baseTotalRevenue + $miscRev;
                                $profit = $totalRev - $totalExp;

                                return Number::currency($profit, 'EGP', precision: 0);
                            }),

                        Placeholder::make('profit_margin')
                            ->label('هامش الربح')
                            ->content(function (Get $get) use ($baseTotalCost, $baseFeedCost, $baseOperatingExpenses, $baseTotalRevenue) {
                                $transactions = $get('misc_transactions') ?? [];
                                $miscExp = collect($transactions)->where('type', 'expense')->sum('amount');
                                $miscRev = collect($transactions)->where('type', 'revenue')->sum('amount');

                                $totalExp = $baseTotalCost + $baseFeedCost + $baseOperatingExpenses + $miscExp;
                                $totalRev = $baseTotalRevenue + $miscRev;

                                if ($totalRev <= 0) {
                                    return '0%';
                                }

                                $margin = (($totalRev - $totalExp) / $totalRev) * 100;

                                return number_format($margin, 2).'%';
                            }),
                    ]),

                Section::make('معلومات الإقفال والتسويات')
                    ->columns(1)
                    ->schema([
                        Repeater::make('misc_transactions')
                            ->label('التسويات المالية الإضافية (إيرادات ومصروفات أخرى)')
                            ->schema([
                                Select::make('type')
                                    ->label('النوع')
                                    ->options([
                                        'revenue' => 'إيراد',
                                        'expense' => 'مصروف',
                                    ])
                                    ->required()
                                    ->live(debounce: 500)
                                    ->native(false),
                                TextInput::make('description')
                                    ->label('البيان')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('amount')
                                    ->label('المبلغ')
                                    ->numeric()
                                    ->required()
                                    ->live(debounce: 500)
                                    ->suffix('ج.م.'),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('إضافة تسوية مالية')
                            ->live(debounce: 500),

                        Textarea::make('closure_notes')
                            ->label('ملاحظات الإقفال')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function closeBatch(): void
    {
        $data = $this->form->getState();

        // Perform the closure logic
        $batch = $this->record;

        $batch->update([
            'is_cycle_closed' => true,
            'closure_date' => now(),
            'misc_transactions' => $data['misc_transactions'] ?? [],

            // Trigger recalculation of attributes based on the new misc values before saving
            'total_feed_cost' => $batch->total_feed_cost,
        ]);

        $batch->update([
            'total_operating_expenses' => $batch->allocated_expenses,
            'total_revenue' => $batch->total_revenue,
            'net_profit' => $batch->net_profit,
            'closed_by' => auth()->id(),
            'closure_notes' => $data['closure_notes'] ?? null,
        ]);

        Notification::make()
            ->success()
            ->title('تم إقفال الدورة بنجاح')
            ->send();

        // Send Telegram Notification
        $chats = TelegraphChat::all();
        if ($chats->isNotEmpty()) {
            $currency = function ($value) {
                return number_format((float) $value).' EGP';
            };

            $message = "🐟 <b><u>إغلاق دورة إنتاج جديدة</u></b> 🐟\n\n".
                "🏷 <b>كود الدورة:</b> <code>{$batch->batch_code}</code>\n".
                '👤 <b>بواسطة:</b> <code>'.(auth()->user()->name ?? 'System')."</code>\n".
                "━━━━━━━━━━━━━━━━━━\n".
                "💰 <b>إجمالي التكاليف الأساسية:</b> {$currency($batch->total_cycle_expenses)}\n".
                "💵 <b>إجمالي الإيرادات الأساسية:</b> {$currency($batch->total_revenue)}\n";

            if (! empty($batch->misc_transactions)) {
                $message .= "━━━━━━━━━━━━━━━━━━\n📋 <b>التسويات الإضافية:</b>\n";
                foreach ($batch->misc_transactions as $tx) {
                    $icon = $tx['type'] === 'revenue' ? '🟢' : '🔴';
                    $message .= "{$icon} {$tx['description']}: {$currency($tx['amount'])}\n";
                }
            }

            $message .= "━━━━━━━━━━━━━━━━━━\n".
                "📈 <b>صافي الربح:</b> {$currency($batch->net_profit)}\n".
                '📊 <b>هامش الربح:</b> '.number_format((float) $batch->profit_margin, 2)."%\n\n".
                '<i>تم حفظ البيانات وإقفال الدورة بنجاح.</i>';

            foreach ($chats as $chat) {
                $chat->html($message)->send();
            }
        }

        $this->redirect($this->getResource()::getUrl('view', ['record' => $batch]));
    }
}
