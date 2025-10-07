<?php

namespace App\Filament\Resources\MortgageRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'Installments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step products and price
                    Step::make('Installments')
                        ->schema([
                            Forms\Components\TextInput::make('no_of_payment')
                                ->required()
                                ->label('No. Pembayaran')
                                ->helperText('Pembayaran Cicilan Keberapa'),
                            Select::make('sub_total_amount')
                                ->label('Monthly Payment')
                                ->options(function () {
                                    $morgageRequest = $this->getOwnerRecord();
                                    return $morgageRequest
                                        ? [$morgageRequest->monthly_amount => $morgageRequest->monthly_amount]
                                        : [];
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    // pajak
                                    $tax = $state * 0.11;
                                    // sub total 
                                    $sub_total_amount = $state;
                                    // fixed insurance
                                    $insurance = 90000;
                                    // grand total
                                    $grand_total = $state + $tax + $insurance;

                                    $set('total_taxt_amount', round($tax));
                                    $set('insurance_amount', $insurance);
                                    $set('grand_total_amount', round($grand_total));

                                    $morgageRequest = $this->getOwnerRecord();
                                    if ($morgageRequest) {
                                        $lastInstallment = $morgageRequest->Installments()
                                            ->where('is_paid', true)
                                            ->orderBy('no_of_payment', 'desc')
                                            ->first();
                                        $previousRemainingLoan = $lastInstallment ? $lastInstallment->remaining_loan_amount : $morgageRequest->loan_interest_total_amount;
                                        $remainingLoanAfterPayment = max($previousRemainingLoan - round($sub_total_amount), 0);
                                        // set the calculated remaining loan
                                        $set('remaining_loan_amount', $remainingLoanAfterPayment);
                                        $set('remaining_loan_amount_before_payment', $previousRemainingLoan);
                                    }
                                }),
                            // form for total tax amount
                            TextInput::make('total_taxt_amount')
                                ->label('Tax 11%')
                                ->readOnly()
                                ->required()
                                ->numeric()
                                ->prefix('IDR'),
                            TextInput::make('insurance_amount')
                                ->label('Additional Insurance')
                                ->readOnly()
                                ->numeric()
                                ->default(900000)
                                ->prefix('IDR')
                                ->required(),
                            TextInput::make('grand_total_amount')
                                ->label('Total Payment')
                                ->readOnly()
                                ->numeric()
                                ->prefix('IDR')
                                ->required(),
                            TextInput::make('remaining_loan_amount_before_payment')
                                ->label('Remaining Loan Before Payment')
                                ->readOnly()
                                ->prefix('IDR'),
                            TextInput::make('remaining_loan_amount')
                                ->label('Remaining Loan After Payment')
                                ->readOnly()
                                ->prefix('IDR')
                                ->numeric(),
                        ]),
                    Step::make('Payments Methods')
                        ->schema([
                            ToggleButtons::make('is_paid')
                            ->label('Payment Status')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-s-check-circle',
                                false => 'heroicon-s-x-circle',
                            ])
                            ->required(),

                            Select::make('payment_type')
                            ->label('Payment Method')
                            ->options([
                                'Midtrans' => 'Midtrans',
                                'Manual' => 'Manual',
                            ])
                            ->required(),
                            FileUpload::make('proof')
                            ->label('Payment proof')
                            ->image(),
                        ]),
                ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->skippable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('no_of_payment')
            ->columns([
                Tables\Columns\TextColumn::make('no_of_payment'),
                Tables\Columns\TextColumn::make('sub_total_amount'),
                Tables\Columns\TextColumn::make('insurance_amount'),
                Tables\Columns\TextColumn::make('total_taxt_amount'),
                Tables\Columns\TextColumn::make('remaining_loan_amount')
                    ->label('Remaining Loan'),
                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Verified'),
                
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
