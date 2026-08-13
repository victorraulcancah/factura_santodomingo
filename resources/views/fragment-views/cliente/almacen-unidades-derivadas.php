<?php ?>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Unidades Derivadas</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Almacén</a></li>
                <li class="breadcrumb-item active">Unidades Derivadas</li>
            </ol>
        </div>
    </div>
</div>

<div id="container-vue">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Catálogo de Unidades Derivadas</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-unidad">
                        <i class="fa fa-plus"></i> Nueva Unidad
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>¿Qué es una Unidad Derivada?</strong> Es un grupo o presentación que contiene varias
                        unidades base. Ej: <em>Caja</em>, <em>Docena</em>, <em>Pack</em>.
                        Al crear un producto eliges la unidad derivada y dices cuántas unidades base trae.
                    </div>

                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width:80px;">ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th style="width:140px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in lista" :key="item.id_unidad">
                                <td>{{ item.id_unidad }}</td>
                                <td><strong>{{ item.nombre }}</strong></td>
                                <td>{{ item.descripcion }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" @click="abrirEditar(item)">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" @click="eliminar(item)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="lista.length === 0">
                                <td colspan="4" class="text-center text-muted">No hay unidades derivadas. Crea la primera.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar -->
    <div class="modal fade" id="modal-add-unidad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Unidad Derivada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input v-model="nuevo.nombre" required type="text" class="form-control" placeholder="Ej: Caja, Docena, Pack">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input v-model="nuevo.descripcion" type="text" class="form-control" placeholder="Opcional">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modal-edt-unidad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Unidad Derivada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="actualizar">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input v-model="editar.nombre" required type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input v-model="editar.descripcion" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const app = new Vue({
            el: "#container-vue",
            data: {
                lista: [],
                nuevo: { nombre: '', descripcion: '' },
                editar: { id_unidad: 0, nombre: '', descripcion: '' }
            },
            mounted() {
                this.cargar();
            },
            methods: {
                cargar() {
                    _ajax("/ajs/unidades-derivadas/listar", "POST", {}, (resp) => {
                        if (resp.res) this.lista = resp.data;
                    });
                },
                guardar() {
                    if (!this.nuevo.nombre.trim()) return;
                    _ajax("/ajs/unidades-derivadas/add", "POST", this.nuevo, (resp) => {
                        if (resp.res) {
                            $("#modal-add-unidad").modal("hide");
                            this.nuevo = { nombre: '', descripcion: '' };
                            alertExito("Unidad creada").then(() => this.cargar());
                        } else {
                            alertAdvertencia(resp.error || "No se pudo crear");
                        }
                    });
                },
                abrirEditar(item) {
                    this.editar = {
                        id_unidad: item.id_unidad,
                        nombre: item.nombre,
                        descripcion: item.descripcion || ''
                    };
                    $("#modal-edt-unidad").modal("show");
                },
                actualizar() {
                    _ajax("/ajs/unidades-derivadas/edt", "POST", this.editar, (resp) => {
                        if (resp.res) {
                            $("#modal-edt-unidad").modal("hide");
                            alertExito("Actualizado").then(() => this.cargar());
                        } else {
                            alertAdvertencia(resp.error || "No se pudo actualizar");
                        }
                    });
                },
                eliminar(item) {
                    Swal.fire({
                        title: '¿Eliminar "' + item.nombre + '"?',
                        text: 'No se podrá eliminar si hay productos usándola.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((r) => {
                        if (!r.isConfirmed) return;
                        _ajax("/ajs/unidades-derivadas/del", "POST", { id_unidad: item.id_unidad }, (resp) => {
                            if (resp.res) {
                                alertExito("Eliminada").then(() => this.cargar());
                            } else {
                                alertAdvertencia(resp.error || "No se pudo eliminar");
                            }
                        });
                    });
                }
            }
        });
    });
</script>
