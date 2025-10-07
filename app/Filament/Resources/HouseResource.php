<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers;
use App\Models\Facility;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\select;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    // menggabungkan resource dengan navigation
    protected static ?string $navigationGroup = 'Products';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationBadgeTooltip = 'The number of House';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength((255)),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),
                        Select::make('certificate')
                            ->options([
                                'SHM' => 'SHM',
                                'SHGB' => 'SHGB',
                                'Patches' => 'Patches',
                            ])
                            ->required(),
                        FileUpload::make('thumbnail')
                            ->image()
                            ->required(),
                        Repeater::make('photo')
                            ->relationship('photos')
                            ->schema([
                                FileUpload::make('photo')
                                    ->required(),
                            ]),
                        Repeater::make('facilities')
                            ->relationship('facilities')
                            ->schema([
                                select::make('facility_id')
                                    ->label('Facility')
                                    ->options(Facility::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ]),

                        Fieldset::make('Additional')
                            ->schema([
                                Textarea::make('about')
                                    ->required(),
                                Select::make('city_id')
                                    ->relationship('city', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('electric')
                                    ->required()
                                    ->prefix('Watt'),
                                TextInput::make('land_area')
                                    ->required()
                                    ->numeric()
                                    ->prefix('m2'),
                                TextInput::make('building_area')
                                    ->required()
                                    ->numeric()
                                    ->prefix('m2'),
                                TextInput::make('bedroom')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Unit'),
                                TextInput::make('bathroom')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Unit'),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category.name'),
                TextColumn::make('city.name'),
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
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            'edit' => Pages\EditHouse::route('/{record}/edit'),
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
