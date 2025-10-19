<?php

namespace App\Filament\Resources\ReviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('revision_status')
                    ->options([
                        'done' => 'مكتمل',
                        'not_done' => 'غير مكتمل',
                        'partially_done' => 'مكتمل جزئياً',
                    ])
                    ->label('حالة المراجعة')
                    ->native(false)
                    ->default('done')
                    ->columnSpan(1),

                Forms\Components\DatePicker::make('revision_date')
                    ->label('تاريخ المراجعة')
                    ->default(now())
                    ->maxDate(now())
                    ->columnSpan(1),

                Forms\Components\Textarea::make('notes_for_author')
                    ->label('ملاحظات للمؤلف')
                    ->rows(2)
                    ->placeholder('ملاحظات توجيهية للمؤلف حول التعديلات المطلوبة')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('notes_for_editor')
                    ->label('ملاحظات للمحرر')
                    ->rows(2)
                    ->placeholder('ملاحظات داخلية لفريق التحرير')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('الملف المعدل')
                    ->directory('revision_files')
                    ->preserveFilenames()
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('note')
                    ->label('ملاحظة مرافقة')
                    ->rows(2)
                    ->placeholder('شرح محتوى التعديل')
                    ->columnSpanFull(),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revision_date')
                    ->label('التاريخ')
                    ->date(),

                Tables\Columns\TextColumn::make('revision_status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'done' => 'مكتمل',
                        'partially_done' => 'تم الإنجاز جزئياً',
                        'not_done' => 'غير مكتمل',
                        default => 'غير محدد',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'done' => 'success',
                        'partially_done' => 'warning',
                        'not_done' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes_for_author')
                    ->label('ملاحظات للمؤلف')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مراجعة تعديل'),
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
