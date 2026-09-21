<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                <i class="bi bi-people-fill"></i>
            </span>
            {{ config('app.name', 'MiGestión Cooperadoras') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Inicio
                    </x-nav-link>
                </li>

                @can('gestionar establecimiento')
                    <li class="nav-item">
                        <x-nav-link :href="route('establecimiento.edit')" :active="request()->routeIs('establecimiento.edit')">
                            Establecimiento
                        </x-nav-link>
                    </li>
                @endcan

                @can('ver socios')
                    <li class="nav-item">
                        <x-nav-link :href="route('socios.index')" :active="request()->routeIs('socios.*')">
                            Socios
                        </x-nav-link>
                    </li>
                @endcan

                @can('ver comision directiva')
                    <li class="nav-item">
                        <x-nav-link :href="route('comision.index')" :active="request()->routeIs('comision.*')">
                            Comisión Directiva
                        </x-nav-link>
                    </li>
                @endcan

                @can('ver tesoreria')
                    <li class="nav-item">
                        <x-nav-link :href="route('tesoreria.index')" :active="request()->routeIs('tesoreria.*')">
                            Tesorería
                        </x-nav-link>
                    </li>
                @endcan

                @can('ver asambleas')
                    <li class="nav-item">
                        <x-nav-link :href="route('asambleas.index')" :active="request()->routeIs('asambleas.*')">
                            Asambleas
                        </x-nav-link>
                    </li>
                @endcan

                @can('ver bienes')
                    <li class="nav-item">
                        <x-nav-link :href="route('bienes.index')" :active="request()->routeIs('bienes.*')">
                            Bienes
                        </x-nav-link>
                    </li>
                @endcan

                @can('ver reportes')
                    <li class="nav-item">
                        <x-nav-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')">
                            Reportes
                        </x-nav-link>
                    </li>
                @endcan
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Cerrar sesión
                            </a>
                            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
