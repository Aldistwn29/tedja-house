<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MortgageRequestResource\Pages;
use App\Filament\Resources\MortgageRequestResource\RelationManagers;
use App\Filament\Resources\MortgageRequestResource\RelationManagers\InstallmentsRelationManager;
use App\Models\House;
use App\Models\Interest;
use App\Models\MortgageRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MortgageRequestResource extends Resource
{
    protected static ?string $model = MortgageRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    // menggabungkan resource dengan navigation
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step products and price
                    Step::make('products and Price')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('house_id')
                                        ->label('House')
                                        ->options(House::query()->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $house = House::find($state);
                                            if ($house) {
                                                $set('house_price', $house->price ?? 0);
                                            }
                                        }),
                                    // select for interest
                                    Select::make('interest_id')
                                        ->label('Annual Interest %')
                                        ->options(function (callable $get) {
                                            $houseId = $get('house_id');
                                            if ($houseId) {
                                                return Interest::where('house_id', $houseId)
                                                    ->get()
                                                    ->pluck('interest', 'id');
                                            }
                                            return [];
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $interest = Interest::find($state);
                                            if ($interest) {
                                                $set('bank_name', $interest->bank->name ?? 'tidak ada bank');
                                                $set('interest', $interest->interest);
                                                $set('duration', $interest->duration);
                                            }
                                        }),
                                    // form for name bank
                                    TextInput::make('bank_name')
                                        ->label('Bank Name')
                                        ->readOnly()
                                        ->required(),
                                    // form for duration
                                    TextInput::make('duration')
                                        ->label('Duration of Years')
                                        ->readOnly()
                                        ->required()
                                        ->numeric()
                                        ->suffix('Years'),
                                    // form for interest
                                    TextInput::make('interest')
                                        ->label('Interest %')
                                        ->readOnly()
                                        ->required()
                                        ->numeric()
                                        ->suffix('%'),
                                    // form for house price
                                    TextInput::make('house_price')
                                        ->label('House Price')
                                        ->readOnly()
                                        ->required()
                                        ->prefix('IDR')
                                        ->numeric(),
                                    Select::make('dp_percentage')
                                        ->label('Payment of Percentage')
                                        ->options([
                                            5 => '5%',
                                            10 => '10%',
                                            15 => '15%',
                                            20 => '20%',
                                            40 => '40%',
                                            50 => '50%',
                                            60 => '60%',
                                            80 => '80%',
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $housePrice = $get('house_price') ?? 0;
                                            $dpAmount = ($state / 100) * $housePrice;
                                            $loanAmount = max($housePrice - $dpAmount, 0);

                                            $set('dp_total_amount', round($dpAmount));
                                            $set('loan_total_amount', round($loanAmount));

                                            // calaculate monthly payment
                                            $durationYears = $get('duration') ?? 0;
                                            $interestRate = $get('interest') ?? 0;

                                            if ($durationYears > 0 && $loanAmount > 0 && $interestRate > 0) {
                                                $totalPayment = $durationYears * 12;
                                                $monthlyInterestRate = $interestRate / 100 / 12;
                                                // Amount formula
                                                $numerator = $loanAmount * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $totalPayment);
                                                $denominator = pow(1 + $monthlyInterestRate, $totalPayment) - 1;
                                                $monthlyPayment = $denominator > 0 ? $numerator / $denominator : 0;
                                                $set('monthly_amount', round($monthlyPayment));
                                                // Total loan with interest
                                                $loanInterestTotalAmount = $monthlyPayment * $totalPayment;
                                                $set('loan_interest_total_amount', round($loanInterestTotalAmount));
                                            } else {
                                                $set('monthly_amount', 0);
                                                $set('loan_interest_total_amount', 0);
                                            }
                                        }),
                                    // Down Payment 
                                    TextInput::make('dp_total_amount')
                                        ->label('Down Payment Amount')
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('IDR'),
                                    // Loan Amount
                                    TextInput::make('loan_total_amount')
                                        ->label('Loan Amount')
                                        ->readOnly()
                                        ->required()
                                        ->numeric()
                                        ->prefix('IDR'),
                                    // Monthly amount
                                    TextInput::make('monthly_amount')
                                        ->label('Monthly Amount')
                                        ->numeric()
                                        ->prefix('IDR'),
                                    // Total loan with interest
                                    TextInput::make('loan_interest_total_amount')
                                        ->label('Total Payment Amount')
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('IDR'),
                                ]),
                        ]),

                    // step Customer information
                    Step::make('Customer Information')
                        ->schema([
                            Select::make('user_id')
                                ->relationship('customer', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $user = User::find($state);
                                    $name = $user->name;
                                    $email = $user->email;

                                    $set('name', $name);
                                    $set('email', $email);
                                })
                                ->afterStateHydrated(function (callable $set, $state) {
                                    $userId = $state;
                                    if ($userId) {
                                        $user = User::find($userId);
                                        $name = $user->name;
                                        $email = $user->email;
                                        $set('name', $name);
                                        $set('email', $email);
                                    }
                                }),
                            // form for name
                            TextInput::make('name')
                                ->required()
                                ->readOnly()
                                ->maxLength(255),
                            // form for email
                            TextInput::make('email')
                                ->required()
                                ->readOnly()
                                ->maxLength(255),
                        ]),
                    // Step bank Approval
                    Step::make('Bank Approval')
                        ->schema([
                            FileUpload::make('document')
                                ->label('Upload Document')
                                ->helperText('Format: PDF, PNG, Document')
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'image/png',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                ])
                                ->required(),
                            Select::make('status')
                                ->options([
                                    'Waiting for Bank' => 'waiting for bank',
                                    'Approved' => 'approved',
                                    'Rejected' => 'rejected',
                                ]),
                        ])
                ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('house.thumbnail'),
                TextColumn::make('house.name')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('bank_name'),
                TextColumn::make('status')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download Document')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (MortgageRequest $record) => asset('storage/' .$record->document))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Memanggil relationship installments
            InstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMortgageRequests::route('/'),
            'create' => Pages\CreateMortgageRequest::route('/create'),
            'edit' => Pages\EditMortgageRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
