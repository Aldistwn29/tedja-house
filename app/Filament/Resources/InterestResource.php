<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterestResource\Pages;
use App\Filament\Resources\InterestResource\RelationManagers;
use App\Models\Interest;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InterestResource extends Resource
{
    protected static ?string $model = Interest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    // menggabungkan resource dengan navigation
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('house_id')
                ->relationship('house', 'name')
                ->searchable()
                ->preload()
                ->required(),
                Select::make('bank_id')
                ->relationship('bank', 'name')
                ->searchable()
                ->preload()
                ->required(),
                TextInput::make('interest')
                ->required()
                ->numeric()
                ->prefix('%'),
                TextInput::make('duration')
                ->required()
                ->numeric()
                ->prefix('Years'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('house.thumbnail'),
                TextColumn::make('house.name')
                ->searchable(),
                TextColumn::make('bank.name'),
                TextColumn::make('interest'),
                TextColumn::make('duration'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterests::route('/'),
            'create' => Pages\CreateInterest::route('/create'),
            'edit' => Pages\EditInterest::route('/{record}/edit'),
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
