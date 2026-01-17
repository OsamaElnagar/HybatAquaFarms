<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class ManualJournalEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';

    protected string $view = 'filament.pages.manual-journal-entry';

    protected static ?string $title = 'إنشاء قيد يدوي';

    public ?array $data = [];

    public static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'قيد يدوي';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة و المالية';
    }

    public function mount(): void
    {
        $this->form->fill([
            'date' => now()->toDateString(),
            'lines' => [
                ['account_id' => null, 'debit' => 0, 'credit' => 0, 'description' => ''],
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),

                Textarea::make('description')
                    ->label('الوصف')
                    ->placeholder('مثال: الأرصدة الافتتاحية - 2025/01/01')
                    ->rows(2),

                Repeater::make('lines')
                    ->label('بنود القيد')
                    ->schema([
                        Select::make('account_id')
                            ->label('الحساب')
                            ->options(Account::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('debit')
                            ->label('مدين')
                            ->numeric()
                            ->suffix('EGP')
                            ->default(0)
                            ->live(),

                        TextInput::make('credit')
                            ->label('دائن')
                            ->numeric()
                            ->default(0)
                            ->suffix('EGP')
                            ->live(),

                        TextInput::make('description')
                            ->label('البيان')
                            ->columnSpan(2),
                    ])
                    ->columns(6)
                    ->defaultItems(1)
                    ->addActionLabel('إضافة بند')
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(
                        fn (array $state): ?string => Account::find($state['account_id'])?->name ?? 'بند جديد'
                    ),

                // ViewField::make('totals')
                //     ->view('filament.forms.components.journal-entry-totals'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('حفظ القيد')
                ->color('primary')
                ->icon('heroicon-o-check')
                ->submit('create'),

            Action::make('reset')
                ->label('إعادة تعيين')
                ->action('resetForm')
                ->color('gray')
                ->icon('heroicon-o-arrow-path'),
        ];
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Validate totals
        $totalDebit = collect($data['lines'])->sum('debit');
        $totalCredit = collect($data['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            Notification::make()
                ->title('خطأ في التوازن')
                ->body("إجمالي المدين ({$totalDebit}) لا يساوي إجمالي الدائن ({$totalCredit})")
                ->danger()
                ->send();

            return;
        }

        if ($totalDebit == 0 || $totalCredit == 0) {
            Notification::make()
                ->title('خطأ')
                ->body('يجب إدخال مبالغ في القيد')
                ->danger()
                ->send();

            return;
        }

        try {
            DB::transaction(function () use ($data) {
                // Create journal entry
                $entry = JournalEntry::create([
                    'date' => $data['date'],
                    'description' => $data['description'] ?? 'قيد يدوي',
                    'is_posted' => true,
                    'posted_by' => auth('web')->id(),
                    'posted_at' => now(),
                    'source_type' => self::class,
                    'source_id' => 0,
                ]);

                // Create journal lines
                foreach ($data['lines'] as $line) {
                    if ($line['debit'] > 0 || $line['credit'] > 0) {
                        JournalLine::create([
                            'journal_entry_id' => $entry->id,
                            'account_id' => $line['account_id'],
                            'debit' => $line['debit'] ?? 0,
                            'credit' => $line['credit'] ?? 0,
                            'description' => $line['description'] ?? null,
                        ]);
                    }
                }
            });

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم إنشاء القيد اليدوي')
                ->success()
                ->send();

            // Reset form
            $this->resetForm();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء الحفظ: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->mount();
    }
}
