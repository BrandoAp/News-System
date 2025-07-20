<?php 
require_once  '../src/modules/noticia.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicion ? 'Editar' : 'Crear' ?> Noticia</title>
    <link rel="stylesheet" href="/News-System/public/css/noticias.css">
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
    <!-- Header con gradiente -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 shadow-2xl">
        <div class="container mx-auto px-6 py-8">
            <div class="flex items-center justify-between">
                <h1 class="text-4xl font-black text-white tracking-tight">
                    <?= $modo_edicion ? '✏️ Editar Noticia' : '📝 Crear Nueva Noticia' ?>
                </h1>
                <a href="indexnoticia.php" 
                   class="bg-white/20 hover:bg-white/30 text-white font-semibold py-2 px-6 rounded-full transition-all duration-300 hover:scale-105 backdrop-blur-sm">
                    ← Volver
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 -mt-4 pb-12">
        <!-- Card principal con efecto glass -->
        <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden max-w-5xl mx-auto">
            
            <!-- Alertas mejoradas -->
            <?php if (!empty($errores['general'])): ?>
                <div class="m-6 bg-gradient-to-r from-red-500 to-red-600 text-white p-4 rounded-2xl shadow-lg fade-in">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">⚠️</span>
                        <div>
                            <p class="font-semibold">Error</p>
                            <p class="text-red-100"><?= htmlspecialchars($errores['general']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['errores_imagenes'])): ?>
                <div class="m-6 bg-gradient-to-r from-amber-500 to-orange-500 text-white p-4 rounded-2xl shadow-lg fade-in">
                    <div class="flex items-start">
                        <span class="text-2xl mr-3 mt-1">⚠️</span>
                        <div>
                            <p class="font-semibold mb-2">Advertencias con imágenes:</p>
                            <ul class="space-y-1 text-amber-100">
                                <?php foreach ($_SESSION['errores_imagenes'] as $error): ?>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span><?= htmlspecialchars($error) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['errores_imagenes']); ?>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="gestionar_noticia.php<?= $modo_edicion ? '?id=' . $noticia['id'] : '' ?>" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="p-8 space-y-8"
                  x-data="{ 
                      previewImages: [], 
                      dragOver: false,
                      publishNow: <?= ($modo_edicion && $noticia['id_estado'] == $noticiasController->obtenerIdEstadoPorNombre('publicado')) ? 'true' : 'true' ?>,
                      resumenCount: <?= strlen($noticia['resumen'] ?? '') ?>,
                      maxResumen: 250
                  }">
                
                <?php if ($modo_edicion): ?>
                    <input type="hidden" name="id" value="<?= $noticia['id'] ?>">
                <?php endif; ?>

                <!-- Grid de campos principales -->
                <div class="grid lg:grid-cols-2 gap-8">
                    
                    <!-- Título -->
                    <div class="lg:col-span-2">
                        <label for="titulo" class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                            <span class="text-lg mr-2">📰</span>
                            Título de la Noticia
                        </label>
                        <input type="text" 
                               id="titulo" 
                               name="titulo" 
                               value="<?= htmlspecialchars($noticia['titulo']) ?>" 
                               class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 text-lg font-medium placeholder-gray-400 <?= isset($errores['titulo']) ? 'border-red-500 bg-red-50' : 'bg-white hover:border-gray-300' ?>"
                               placeholder="Escribe un título atractivo...">
                        <?php if (isset($errores['titulo'])): ?>
                            <p class="mt-2 text-red-600 text-sm font-medium flex items-center">
                                <span class="mr-1">⚠️</span>
                                <?= $errores['titulo'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Autor -->
                    <div>
                        <label for="autor" class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                            <span class="text-lg mr-2">👤</span>
                            Autor
                        </label>
                        <input type="text" 
                               id="autor" 
                               name="autor" 
                               value="<?= htmlspecialchars($noticia['autor']) ?>" 
                               class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 placeholder-gray-400 <?= isset($errores['autor']) ? 'border-red-500 bg-red-50' : 'bg-white hover:border-gray-300' ?>"
                               placeholder="Nombre del autor...">
                        <?php if (isset($errores['autor'])): ?>
                            <p class="mt-2 text-red-600 text-sm font-medium flex items-center">
                                <span class="mr-1">⚠️</span>
                                <?= $errores['autor'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label for="id_categoria" class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                            <span class="text-lg mr-2">🏷️</span>
                            Categoría
                        </label>
                        <select id="id_categoria" 
                                name="id_categoria" 
                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 <?= isset($errores['categoria']) ? 'border-red-500 bg-red-50' : 'bg-white hover:border-gray-300' ?>">
                            <option value="">-- Selecciona una categoría --</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($noticia['id_categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['categoria'])): ?>
                            <p class="mt-2 text-red-600 text-sm font-medium flex items-center">
                                <span class="mr-1">⚠️</span>
                                <?= $errores['categoria'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- NUEVO CAMPO: Resumen -->
                <div>
                    <label for="resumen" class="block text-sm font-bold text-gray-700 mb-3 flex items-center justify-between">
                        <span class="flex items-center">
                            <span class="text-lg mr-2">📋</span>
                            Resumen de la Noticia
                        </span>
                        <span class="text-sm font-normal" 
                              :class="resumenCount > maxResumen ? 'text-red-500' : resumenCount > maxResumen * 0.8 ? 'text-amber-500' : 'text-gray-500'">
                            <span x-text="resumenCount"></span>/<span x-text="maxResumen"></span>
                        </span>
                    </label>
                    <textarea id="resumen" 
                              name="resumen" 
                              rows="4" 
                              maxlength="250"
                              x-on:input="resumenCount = $event.target.value.length"
                              class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 resize-y placeholder-gray-400 <?= isset($errores['resumen']) ? 'border-red-500 bg-red-50' : 'bg-white hover:border-gray-300' ?>"
                              placeholder="Escribe un breve resumen que capture la esencia de la noticia (máximo 250 caracteres)..."><?= htmlspecialchars($noticia['resumen'] ?? '') ?></textarea>
                    
                    <div class="mt-2 flex justify-between items-center">
                        <p class="text-sm text-gray-600">
                            💡 Este resumen se mostrará en las vistas previas y tarjetas de noticias
                        </p>
                        <?php if (isset($errores['resumen'])): ?>
                            <p class="text-red-600 text-sm font-medium flex items-center">
                                <span class="mr-1">⚠️</span>
                                <?= $errores['resumen'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contenido -->
                <div>
                    <label for="contenido" class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                        <span class="text-lg mr-2">📝</span>
                        Contenido de la Noticia
                    </label>
                    <textarea id="contenido" 
                              name="contenido" 
                              rows="12" 
                              class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 resize-y placeholder-gray-400 <?= isset($errores['contenido']) ? 'border-red-500 bg-red-50' : 'bg-white hover:border-gray-300' ?>"
                              placeholder="Escribe el contenido completo de la noticia..."><?= htmlspecialchars($noticia['contenido']) ?></textarea>
                    <?php if (isset($errores['contenido'])): ?>
                        <p class="mt-2 text-red-600 text-sm font-medium flex items-center">
                            <span class="mr-1">⚠️</span>
                            <?= $errores['contenido'] ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Imágenes existentes (solo en modo edición) -->
                <?php if ($modo_edicion && !empty($imagenes_noticia)): ?>
                    <div class="border-t border-gray-200 pt-8">
                        <h3 class="text-lg font-bold text-gray-700 mb-6 flex items-center">
                            <span class="text-xl mr-3">🖼️</span>
                            Imágenes Actuales
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php foreach ($imagenes_noticia as $imagen): ?>
                                <div class="imagen-preview fade-in">
                                    <img src="../uploads/imagenes/<?= htmlspecialchars($imagen['url_thumbnail']) ?>" 
                                         alt="<?= htmlspecialchars($imagen['descripcion']) ?>"
                                         class="w-full h-40 object-cover transition-transform duration-300 hover:scale-105">
                                    
                                    <button type="button" 
                                            class="btn-eliminar" 
                                            onclick="eliminarImagen(<?= $imagen['id'] ?>)"
                                            title="Eliminar imagen">×</button>
                                    
                                    <?php if ($imagen['es_principal']): ?>
                                        <div class="absolute bottom-2 left-2">
                                            <span class="bg-gradient-to-r from-emerald-500 to-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                                ⭐ Principal
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="absolute bottom-2 left-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-full transition-all duration-200 shadow-lg hover:shadow-xl"
                                                onclick="establecerPrincipal(<?= $imagen['id'] ?>)">
                                            Hacer principal
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Subida de nuevas imágenes -->
                <div class="border-t border-gray-200 pt-8">
                    <label class="block text-sm font-bold text-gray-700 mb-4 flex items-center">
                        <span class="text-xl mr-3">📸</span>
                        <?= $modo_edicion ? 'Agregar Más Imágenes' : 'Imágenes' ?> (hasta 3)
                    </label>
                    
                    <!-- Zona de arrastrar y soltar -->
                    <div class="relative" 
                         x-on:dragover.prevent="dragOver = true" 
                         x-on:dragleave.prevent="dragOver = false"
                         x-on:drop.prevent="dragOver = false; handleDrop($event)">
                        
                        <input type="file" 
                               name="imagenes[]" 
                               multiple 
                               accept="image/*" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                               x-on:change="previewSelectedImages($event)"
                               id="imagen-input">
                        
                        <div class="border-3 border-dashed rounded-3xl p-12 text-center transition-all duration-300"
                             :class="dragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'">
                            <div class="space-y-4">
                                <div class="text-6xl">📁</div>
                                <div>
                                    <p class="text-lg font-semibold text-gray-700">Arrastra imágenes aquí</p>
                                    <p class="text-gray-500">o <span class="text-blue-600 font-medium">haz clic para seleccionar</span></p>
                                </div>
                                <p class="text-sm text-gray-500">
                                    JPG, PNG, GIF, WebP • Máx. 5MB por imagen • Máx. 3 imágenes
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Vista previa de nuevas imágenes -->
                    <div id="preview-container" 
                         class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-6"
                         x-show="previewImages.length > 0"
                         x-transition></div>
                </div>

                <!-- Opciones de publicación -->
                <div class="border-t border-gray-200 pt-8">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="publicar_ahora" 
                                   class="w-5 h-5 text-blue-600 bg-white border-2 border-gray-300 rounded focus:ring-4 focus:ring-blue-500/20"
                                   x-model="publishNow">
                            <div class="ml-4">
                                <span class="text-base font-semibold text-gray-800 flex items-center">
                                    <span class="text-lg mr-2">🚀</span>
                                    Publicar inmediatamente
                                </span>
                                <p class="text-sm text-gray-600 mt-1">
                                    Si no marcas esta opción, la noticia se guardará como borrador.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row gap-4 pt-8 border-t border-gray-200">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-500/50 flex items-center justify-center space-x-2">
                        <span class="text-xl"><?= $modo_edicion ? '💾' : '✨' ?></span>
                        <span><?= $modo_edicion ? 'Actualizar' : 'Crear' ?> Noticia</span>
                    </button>
                    
                    <a href="indexnoticia.php" 
                       class="flex-1 sm:flex-none bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl text-center flex items-center justify-center space-x-2">
                        <span class="text-xl">❌</span>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

<script src="/News-System/src/js/gestionar_noticia.js"></script>
</body>
</html>