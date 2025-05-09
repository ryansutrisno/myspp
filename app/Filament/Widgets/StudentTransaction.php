<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class StudentTransaction extends BaseWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Transaction History';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                Transaction::query()->where('user_id', $user->id)->orderBy('created_at', 'DESC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Transaction Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.name')
                    ->label('Department'),
                Tables\Columns\TextColumn::make('departments.semester')
                    ->label('Semester'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    }),
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti Pembayaran')
                    ->width(450)  // Set the width of the preview
                    ->height(225),
                Tables\Columns\TextColumn::make('departments.cost')
                    ->label('Cost')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at') // Waktu pembuatan
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('Payment')
                    ->label('Payment')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn($record) => url("admin/payment/{$record->id}"))  // Redirect to payment page with transaction ID
                    ->visible(fn($record) => $record->payment_status === 'pending')  // Show only if payment_status is 'pending'
            ]);
    }
}
