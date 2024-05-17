<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Post;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\PostResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PostResource\RelationManagers;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Section::make('Main Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->rules([
                                'required', 'min:3', 'max:150',
                            ])
                            ->live()
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation == 'edit') return;

                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->rules([
                                'required', 'max:150',
                            ])
                            ->unique(ignoreRecord: true),

                        RichEditor::make('body')
                            ->rules(['required'])
                            ->fileAttachmentsDirectory('posts/images')
                            ->columnSpanFull()
                    ]),

                Section::make('Meta')
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->directory('posts/thumbnails'),

                        DateTimePicker::make('published_at')
                            ->nullable(),

                        Checkbox::make('featured'),

                        Select::make('author')
                            ->relationship('author', 'name')
                            ->rules(['required'])
                            ->searchable(),

                        Select::make('categories')
                            ->relationship('categories', 'title')
                            ->rules(['required'])
                            ->multiple()
                            ->preload()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),

                TextColumn::make('title')
                    ->limit(50),

                TextColumn::make('slug')
                    ->limit(10),

                TextColumn::make('author.name'),

                TextColumn::make('body')
                    ->limit(20),

                TextColumn::make('published_at')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                CheckboxColumn::make('featured')
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
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
