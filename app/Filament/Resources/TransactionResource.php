<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Department;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->default(fn() => 'TRX' . mt_rand(1000, 9999)),
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->relationship('users', 'name'),
                Forms\Components\TextInput::make('payment_status')
                    ->readOnly()
                    ->default('pending'),
                Forms\Components\Fieldset::make('Department')
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->required()
                            ->label('Department Name & Semester')
                            ->options(Department::query()->get()->mapWithKeys(fn($department) => [$department->id => $department->name . ' - Semester ' . $department->semester])->toArray())
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($department = Department::find($state)) {
                                    $set('department_cost', $department->cost);
                                } else {
                                    $set('department_cost', null);
                                }
                            })
                    ]),
                Forms\Components\TextInput::make('department_cost')
                    ->label('Cost')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti Pembayaran')
                    ->width(450)
                    ->height(225),
                Tables\Columns\TextColumn::make('departments.name')
                    ->label('Department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.semester')
                    ->label('Semester')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(Transaction $record): bool => $record->payment_status === 'pending')
                    ->action(function (Transaction $record) {
                        $record->update(['payment_status' => 'success']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Transaksi Disetujui')
                    ->modalDescription('Apakah anda yakin ingin menyetujui transaksi ini? Aksi ini tidak bisa dibatalkan.')
                    ->modalButton('Approve'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
