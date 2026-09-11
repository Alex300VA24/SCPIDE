<?php

namespace App\Livewire;

use App\Http\Requests\ActualizarPasswordRequest;
use App\UseCases\Password\ChangeUserPassword;
use Livewire\Component;

final class ActualizarPassword extends Component
{
    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function clearForm(): void
    {
        $this->reset('currentPassword', 'password', 'password_confirmation');
        $this->resetValidation();
    }

    public function update(ChangeUserPassword $changeUserPassword)
    {
        $usuario = auth()->user();

        $this->authorize('updatePassword', $usuario);

        $this->validate(
            ActualizarPasswordRequest::buildRules(),
            ActualizarPasswordRequest::validationMessages(),
            ActualizarPasswordRequest::validationAttributes(),
        );

        $changeUserPassword->handle($usuario, $this->currentPassword, $this->password);

        $this->clearForm();

        $this->dispatch('pide-alert', message: 'Contraseña actualizada correctamente.', type: 'success');
        $this->dispatch('password-updated');
    }

    public function render()
    {
        return view('livewire.actualizar-password');
    }
}
