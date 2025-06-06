<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;

class AdminTransaction extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Transaction History Admin';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->orderBy('created_at', 'DESC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Pengguna')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departments.name')
                    ->label('Departemen'),
                Tables\Columns\TextColumn::make('departments.semester')
                    ->label('Semester'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
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
                    ->label('Biaya')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at') // Waktu pembuatan
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
