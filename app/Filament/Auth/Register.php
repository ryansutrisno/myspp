<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends AuthRegister
{
    protected function getForms(): array
    {

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->label('Phone Number')
                            ->placeholder('Enter your phone number'),
                        FileUpload::make('image')
                            ->image()
                            ->columnSpanFull()
                            ->required()
                            ->label('Profile Picture')
                            ->placeholder('Upload your profile picture'),
                        FileUpload::make('scanijazah')
                            ->image()
                            ->columnSpanFull()
                            ->required()
                            ->label('Scan of Certificate')
                            ->placeholder('Upload your last certificate/ijazah terakhir'),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function submit(): void
    {
        $data = $this->form->getState();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'image' => $data['image'] ?? null,
            'scanijazah' => $data['scanijazah'] ?? null,
        ]);

        Auth::login($user);
    }
}
