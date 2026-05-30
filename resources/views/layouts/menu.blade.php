<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="/home">
        <i class=" fas fa-building"></i><span>Dashboard</span>
    </a>
</li>

@can('ver-facturacion')
<li class="side-menus {{ Request::is('facturacion') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('facturacion.index') }}">
        <i class="fa-solid fa-users"></i><span>Facturación</span>
    </a>
</li>
<li class="side-menus {{ Request::is('facturacion/historial') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('facturacion.historial') }}">
        <i class="fas fa-history"></i><span>Historial transacciones</span>
    </a>
</li>
@endcan

@can('ver-mesa')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('facturacion.mesas') }}">
        <i class="fa-solid fa-users"></i><span>Mesas</span>
    </a>
</li>
@endcan

@can('ver-usuario')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('users.index') }}">
        <i class="fa-solid fa-users"></i><span>Usuarios</span>
    </a>
</li>
@endcan

@can('ver-rol')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('roles.index') }}">
        <i class="fa-solid fa-diagram-project"></i><span>Roles</span>
    </a>
</li>
@endcan

@can('ver-permiso')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('permisos.index') }}">
        <i class="fa-solid fa-lock-open"></i><span>Permisos</span>
    </a>
</li>
@endcan

@can('ver-departamento')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('departamentos.index') }}">
        <i class=" fas fa-building"></i><span>Departamentos</span>
    </a>
</li>
@endcan

@can('ver-ciudad')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('ciudades.index') }}">
        <i class=" fas fa-building"></i><span>Ciudades</span>
    </a>
</li>
@endcan

@can('ver-tipodocumento')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('tiposdocumentos.index') }}">
        <i class=" fas fa-building"></i><span>Tipos de documento</span>
    </a>
</li>
@endcan

@can('ver-socionegocio')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('socios.index') }}">
        <i class=" fas fa-building"></i><span>Socios del negocio</span>
    </a>
</li>
@endcan

@can('ver-transaccion')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('transacciones.index') }}">
        <i class=" fas fa-building"></i><span>Transacciones</span>
    </a>
</li>
@endcan

@can('ver-concepto')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('conceptos.index') }}">
        <i class=" fas fa-building"></i><span>Conceptos</span>
    </a>
</li>
@endcan

@can('ver-categoria')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('categorias.index') }}">
        <i class=" fas fa-building"></i><span>Categorías</span>
    </a>
</li>
@endcan

@can('ver-impuesto')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('impuestos.index') }}">
        <i class=" fas fa-building"></i><span>Impuestos</span>
    </a>
</li>
@endcan

@can('ver-producto')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('productos.index') }}">
        <i class=" fas fa-building"></i><span>Productos</span>
    </a>
</li>
@endcan

@can('ver-formapago')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('formapagos.index') }}">
        <i class=" fas fa-building"></i><span>Formas de pago</span>
    </a>
</li>
@endcan

@can('ver-empresa')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('empresas.index') }}">
        <i class=" fas fa-building"></i><span>Empresa</span>
    </a>
</li>
@endcan

@can('ver-receta')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('recetas.index') }}">
        <i class=" fas fa-building"></i><span>Recetas</span>
    </a>
</li>
@endcan

@can('ver-inventario')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('inventarios.index') }}">
        <i class=" fas fa-building"></i><span>Inventarios</span>
    </a>
</li>
@endcan

<li class="side-menus {{ Request::is('cierres*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('cierres.index') }}">
        <i class="fas fa-calendar-check"></i><span>Cierre de mes</span>
    </a>
</li>

@can('ver-informe')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('informes.index') }}">
        <i class=" fas fa-building"></i><span>Informes</span>
    </a>
</li>
@endcan
