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

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-plus';

    public static function getNavigationLabel(): string
    {
        return 'قيد يدوي';
    }

    protected static ?string $title = 'إنشاء قيد يدوي';

    public static function getNavigationGroup(): ?string
    {
        return 'المالية';
    }

    protected string $view = 'filament.pages.manual-journal-entry';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date' => now()->toDateString(),
            'lines' => [
                ['account_id' => null, 'debit' => 0, 'credit' => 0, 'description' => ''],
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
                            ->default(0)
                            ->suffix('ج.م')
                            ->reactive(),

                        TextInput::make('credit')
                            ->label('دائن')
                            ->numeric()
                            ->default(0)
                            ->suffix('ج.م')
                            ->reactive(),

                        TextInput::make('description')
                            ->label('البيان')
                            ->columnSpan(2),
                    ])
                    ->columns(6)
                    ->defaultItems(2)
                    ->addActionLabel('إضافة بند')
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => Account::find($state['account_id'])?->name ?? 'بند جديد'
                    ),

                ViewField::make('totals')
                    ->view('filament.forms.components.journal-entry-totals'),
            ])
            ->statePath('data');
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
                // Generate entry number
                $lastEntry = JournalEntry::latest('id')->first();
                $number = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;
                $entryNumber = 'JE-'.str_pad($number, 6, '0', STR_PAD_LEFT);

                // Create journal entry
                $entry = JournalEntry::create([
                    'entry_number' => $entryNumber,
                    'date' => $data['date'],
                    'description' => $data['description'] ?? 'قيد يدوي',
                    'is_posted' => true,
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                    'source_type' => self::class,
                    'source_id' => 0, // Manual entry
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
            $this->mount();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء الحفظ: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('حفظ القيد')
                ->action('create')
                ->color('success'),
        ];
    }
}
