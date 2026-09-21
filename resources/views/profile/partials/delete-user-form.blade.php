<section>
    <header>
        <h2 class="h5 fw-semibold">Eliminar cuenta</h2>
        <p class="text-muted small">
            Una vez eliminada tu cuenta, todos sus datos se perderán de forma permanente.
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
        Eliminar cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <h2 class="h5 fw-semibold">¿Seguro que querés eliminar tu cuenta?</h2>

            <p class="text-muted small">
                Ingresá tu contraseña para confirmar que querés eliminar tu cuenta de forma permanente.
            </p>

            <div class="mt-3">
                <x-input-label for="password" value="Contraseña" class="visually-hidden" />
                <x-text-input id="password" name="password" type="password" placeholder="Contraseña" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <x-secondary-button type="button" data-bs-dismiss="modal">
                    Cancelar
                </x-secondary-button>

                <x-danger-button>
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
