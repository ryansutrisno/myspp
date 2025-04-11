<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Biodata extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.biodata';

    public $user;

    public ?array $data = [];

    public function mount(): void
    {
        $this->user = Auth::user();

        $this->form->fill([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'image' => $this->user->image,
            'scanijazah' => $this->user->scanijazah,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->required()->email(),
                        TextInput::make('password')
                            ->password()
                            ->revealable((filament()->arePasswordsRevealable()))
                            ->nullable(),
                        TextInput::make('phone')->required(),
                        FileUpload::make('image')->image()->columnSpanFull(),
                        FileUpload::make('scanijazah')->image()->columnSpanFull(),
                    ])
            ])->statePath('data');
    }

    public function edit(): void
    {
        // validate form data
        $validatedData = $this->form->getState();

        // update user data
        $this->user->name = $validatedData['name'];
        $this->user->email = $validatedData['email'];
        $this->user->phone = $validatedData['phone'];

        // update password if provided
        if (!empty($validatedData['password'])) {
            $this->user->password = Hash::make($validatedData['password']);
        }

        // handle image upload
        if (isset($validatedData['image'])) {
            if ($this->user->image) {
                Storage::delete($this->user->image);
            }
            $this->user->image = $validatedData['image'];
        }

        // handle scanijazah upload
        if (isset($validatedData['scanijazah'])) {
            if ($this->user->scanijazah) {
                Storage::delete($this->user->scanijazah);
            }
            $this->user->scanijazah = $validatedData['scanijazah'];
        }

        // save user data
        $this->user->save();

        // Send notification
        Notification::make()
            ->title('Biodata Updated')
            ->body('Your biodata has been successfully updated.')
            ->success()
            ->send();
    }
}
