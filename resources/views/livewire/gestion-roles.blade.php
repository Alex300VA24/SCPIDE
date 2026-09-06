<div class="modulo-legacy rol-legacy" @notify.window="$refs.notice.textContent = $event.detail.message; $refs.notice.focus()">
    <div class="page-title">
        <h1><i class="fas fa-user-shield"></i> Gestión de Roles</h1>
    </div>
    <p x-ref="notice" tabindex="-1" class="sr-only" aria-live="polite"></p>

    <div class="content-wrapper">
        <div class="tabs">
            <button type="button" wire:click="showCreateTab" class="tab-btn {{ $activeTab === 'create' ? 'active' : '' }}">
                <i class="fas fa-plus"></i> {{ $editingId ? 'Editar Rol' : 'Crear Rol' }}
            </button>
            <button type="button" wire:click="showListTab" class="tab-btn {{ $activeTab === 'list' ? 'active' : '' }}">
                <i class="fas fa-list"></i> Listar Roles
            </button>
        </div>

        @if ($activeTab === 'create')
            <div class="form-section">
                <form wire:submit="save" wire:key="role-form" novalidate>
                    <div class="section-header"><i class="fas fa-info-circle"></i> Información del Rol</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="rolCodigo">Código <span class="required">*</span></label>
                            <input type="text" id="rolCodigo" wire:model="codigo" maxlength="50" placeholder="Ej: VENDEDOR" style="text-transform:uppercase;">
                            @error('codigo')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="rolNombre">Nombre <span class="required">*</span></label>
                            <input type="text" id="rolNombre" wire:model="nombre" maxlength="100" placeholder="Ej: Vendedor">
                            @error('nombre')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="rolNivel">Nivel de Acceso <span class="required">*</span></label>
                            <input type="number" id="rolNivel" wire:model="nivel" min="1" max="10">
                            @error('nivel')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="rolDescripcion">Descripción</label>
                            <textarea id="rolDescripcion" wire:model="descripcion" rows="3" placeholder="Descripción del rol"></textarea>
                            @error('descripcion')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="section-header"><i class="fas fa-puzzle-piece"></i> Módulos Disponibles</div>

                    <div class="modulos-grid" aria-label="Módulos disponibles" x-data="roleModules()">
                        <template x-if="groups.length === 0">
                            <div class="module-empty"><i class="fas fa-puzzle-piece"></i><p>No hay módulos disponibles</p></div>
                        </template>

        <template x-for="group in groups" :key="'role-module-parent-' + group.id">
                            <div class="modulo-padre">
                                <div class="modulo-padre-header">
                                    <input type="checkbox"
                                           :id="'modulo-' + group.id"
                                           class="checkbox-padre"
                                           :checked="nodeState(group) === 'checked'"
                                           x-effect="$el.indeterminate = nodeState(group) === 'indeterminate'"
                                           @click="toggle(group, [])">
                                    <label :for="'modulo-' + group.id">
                                        <i :class="group.icono"></i>
                                        <span class="modulo-info"><strong x-text="group.nombre"></strong><small x-text="group.codigo"></small></span>
                                    </label>
                                </div>

                                <template x-if="group.children.length > 0">
                                    <div class="modulo-hijos">
                                        <template x-for="child in group.children" :key="'role-module-child-' + child.id">
                                            <div class="modulo-hijo-wrap">
                                                <div class="modulo-hijo">
                                                    <input type="checkbox"
                                                           :id="'modulo-' + child.id"
                                                           class="checkbox-hijo"
                                                           :checked="nodeState(child) === 'checked'"
                                                           x-effect="$el.indeterminate = nodeState(child) === 'indeterminate'"
                                                           @click="toggle(child, [group])">
                                                    <label :for="'modulo-' + child.id">
                                                        <i :class="child.icono"></i>
                                                        <span class="modulo-info">
                                                            <strong x-text="child.nombre"></strong>
                                                            <small x-text="child.codigo"></small>
                                                            <span class="modulo-desc" x-show="child.descripcion" x-text="child.descripcion"></span>
                                                        </span>
                                                    </label>
                                                </div>

                                                <template x-if="child.children.length > 0">
                                                    <div class="modulo-nietos">
                                                        <template x-for="grandchild in child.children" :key="'role-module-grandchild-' + grandchild.id">
                                                            <div class="modulo-nieto">
                                                                <input type="checkbox"
                                                                       :id="'modulo-' + grandchild.id"
                                                                       class="checkbox-nieto"
                                                                       :checked="isSel(grandchild.id)"
                                                                       @click="toggle(grandchild, [group, child])">
                                                                <label :for="'modulo-' + grandchild.id">
                                                                    <i :class="grandchild.icono"></i>
                                                                    <span class="modulo-info">
                                                                        <strong x-text="grandchild.nombre"></strong>
                                                                        <small x-text="grandchild.codigo"></small>
                                                                        <span class="modulo-desc" x-show="grandchild.descripcion" x-text="grandchild.descripcion"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    @error('selectedModuleIds')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                    @error('selectedModuleIds.*')<span class="field-error" role="alert">{{ $message }}</span>@enderror

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save"><i class="fas fa-save"></i> {{ $editingId ? 'Actualizar Rol' : 'Crear Rol' }}</span>
                            <span wire:loading wire:target="save"><span class="loading-spinner"></span> Guardando...</span>
                        </button>
                        <button type="button" wire:click="showCreateTab" class="btn btn-secondary"><i class="fas fa-broom"></i> <span>Limpiar</span></button>
                    </div>
                </form>
            </div>
        @else
            <div class="toolbar-row">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por código, nombre o descripción...">
                </div>
                <select wire:model.live="perPage" class="per-page-select" aria-label="Roles por página">
                    <option value="5">5 por página</option>
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                </select>
            </div>

            <div class="table-container" wire:loading.class="is-loading">
                <table id="tablaRoles">
                    <thead><tr><th>Código</th><th>Nombre</th><th>Nivel</th><th>Usuarios</th><th>Módulos</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr wire:key="role-{{ $role->id }}">
                                <td><strong>{{ $role->codigo }}</strong></td>
                                <td>{{ $role->nombre }}</td>
                                <td><span class="badge badge-info">Nivel {{ $role->nivel }}</span></td>
                                <td>{{ $role->usuarios_count }}</td>
                                <td><div class="module-badges">@forelse($role->modulos as $module)
                                    @php
                                        $hash = 0;
                                        foreach (str_split($module->nombre) as $char) { $hash = (($hash << 5) - $hash) + ord($char); $hash &= 0x7fffffff; }
                                        $badgeHue = $hash % 360;
                                    @endphp
                                    <span class="module-badge" style="--hue: {{ $badgeHue }}">{{ $module->nombre }}</span>
                                @empty<span>Sin módulos</span>@endforelse</div></td>
                                <td><span class="badge {{ $role->activo ? 'badge-success' : 'badge-danger' }}">{{ $role->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                <td><div class="action-btns">
                                    <button type="button" class="btn-icon btn-edit" wire:click="openEdit({{ $role->id }})" aria-label="Editar {{ $role->nombre }}" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn-icon btn-delete" wire:click="confirmDelete({{ $role->id }})" aria-label="Eliminar {{ $role->nombre }}" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty-state"><i class="fas fa-inbox"></i><h3>No hay roles registrados</h3><p>Crea tu primer rol desde la pestaña "Crear Rol"</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $roles->links() }}</div>
        @endif
    </div>

    @if ($deleteModalOpen)
        <div data-ui-modal class="modal-backdrop" role="presentation" wire:click.self="closeDeleteModal" @keydown.escape.window="$wire.closeDeleteModal()">
            <section class="modal-panel confirm-modal" role="alertdialog" aria-modal="true" aria-labelledby="delete-role-title">
                <div class="modal-symbol danger"><i class="fas fa-trash"></i></div>
                <h2 id="delete-role-title">Eliminar rol</h2>
                <p>Solo puede eliminarse cuando no tenga usuarios asignados.</p>
                @error('role')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                <div class="action-buttons">
                    <button type="button" wire:click="closeDeleteModal" class="btn btn-secondary">Cancelar</button>
                    <button type="button" wire:click="delete" wire:loading.attr="disabled" wire:target="delete" class="btn btn-primary danger-button">Eliminar</button>
                </div>
            </section>
        </div>
    @endif

    @script
    <script>
        Alpine.data('roleModules', () => ({
                selected: @entangle('selectedModuleIds'),
                groups: @js($moduleGroups),
                isSel(id) {
                    return this.selected.includes(String(id));
                },
                collectIds(node) {
                    return [String(node.id), ...(node.children || []).flatMap((kid) => this.collectIds(kid))];
                },
                nodeState(node) {
                    const kids = node.children || [];
                    if (!kids.length) {
                        return this.isSel(node.id) ? 'checked' : '';
                    }
                    const states = kids.map((kid) => this.nodeState(kid));
                    const allChecked = states.every((s) => s === 'checked');
                    const noneChecked = states.every((s) => s === '');
                    return allChecked ? 'checked' : noneChecked ? '' : 'indeterminate';
                },
                toggle(node, ancestors) {
                    const ids = this.collectIds(node);
                    const turningOn = this.nodeState(node) !== 'checked';
                    if (turningOn) {
                        const addIds = [...ids, ...ancestors.map((a) => String(a.id))];
                        this.selected = Array.from(new Set([...this.selected, ...addIds]));
                        return;
                    }
                    this.selected = this.selected.filter((id) => !ids.includes(id));
                    for (let i = ancestors.length - 1; i >= 0; i--) {
                        if (this.nodeState(ancestors[i]) === '') {
                            const ancestorId = String(ancestors[i].id);
                            this.selected = this.selected.filter((id) => id !== ancestorId);
                        }
                    }
                },
        }));
    </script>
    @endscript
</div>
