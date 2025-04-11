<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class Payment extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.pages.payment';

    public $transaction;
    public ?array $data = [];

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function mount(int $id): void
    {
        // Ambil transaksi berdasarkan ID
        $this->transaction = Transaction::findOrFail($id);

        $this->data = [
            'payment_method' => $this->transaction->payment_method ?? null,
            'payment_proof' => $this->transaction->payment_proof ?? null,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer'
                    ])
                    ->required()
                    ->default($this->data['payment_method']),
                FileUpload::make('payment_proof')
                    ->label('Bukti Pembayaran')
                    ->image()
                    ->required()
                    ->directory('payment_proof')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function edit()
    {
        // validate form data
        $validatedData = $this->form->getState();

        // hapus file lama jika file baru diunggah
        if (isset($validatedData['payment_proof']) && $validatedData['payment_proof'] !== $this->transaction->payment_proof) {
            if ($this->transaction->payment_proof) {
                Storage::delete($this->transaction->payment_proof);
            }
        }
        // update transaksi
        $this->transaction->update([
            'payment_method' => $validatedData['payment_method'],
            'payment_proof' => $validatedData['payment_proof'],
        ]);

        // kirim notifikasi
        Notification::make()
            ->title('Pembayaran Berhasil!')
            ->body('Terima kasih telah melakukan pembayaran, mohon tunggu persetujuan oleh Admin')
            ->success()
            ->send();

        // redirect ke halaman admin
        return redirect('/admin');
    }
}
