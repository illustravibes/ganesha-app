<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Products';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Product Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter the product name'),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique('categories', 'name')
                            ->maxLength(255),
                    ])
                    ->placeholder('Select the product category'),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->placeholder('Enter the product price'),
                        Forms\Components\TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Stock is managed through inventory transactions'),
                    ]),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('size')
                            ->label('Size')
                            ->placeholder('Enter the product size'),
                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->placeholder('Enter the product color'),
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true)
                    ->helperText('Inactive products won\'t appear in reports and sales'),
                Forms\Components\FileUpload::make('attachment')
                    ->label('Product Images')
                    ->directory('products')
                    ->disk('public')
                    ->visibility('public')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->openable()
                    ->downloadable()
                    ->acceptedFileTypes(['image/*'])
                    ->maxFiles(5)
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('600')
                    ->imageResizeTargetHeight('600'),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Enter the product description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Products')
                    ->trueLabel('Active Products')
                    ->falseLabel('Inactive Products'),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn(Builder $query): Builder => $query->where('stock', '<', 10))
                    ->label('Low Stock'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('addStock')
                        ->label('Add Stock')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->url(fn(Product $record): string => route('filament.admin.resources.inventories.create', ['product_id' => $record->id, 'type' => 'in'])),
                    Tables\Actions\Action::make('removeStock')
                        ->label('Remove Stock')
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->url(fn(Product $record): string => route('filament.admin.resources.inventories.create', ['product_id' => $record->id, 'type' => 'out'])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activateProducts')
                        ->label('Activate Products')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->is_active = true;
                                $record->save();
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivateProducts')
                        ->label('Deactivate Products')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->is_active = false;
                                $record->save();
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'category.name', 'description'];
    }
}
