<?php 
require_once  '../src/modules/noticia.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicion ? 'Editar' : 'Crear' ?> Noticia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
   <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.06);
        }
        .input-field {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 1);
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }
        .file-upload-area {
            background: rgba(248, 250, 252, 0.8);
            border: 2px dashed rgba(148, 163, 184, 0.4);
            transition: all 0.3s ease;
        }
        .file-upload-area:hover {
            background: rgba(241, 245, 249, 0.9);
            border-color: rgba(79, 70, 229, 0.5);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <?= $modo_edicion ? 'Editar Noticia' : 'Crear Nueva Noticia' ?>
            </h1>
            <p class="text-gray-600">
                <?= $modo_edicion ? 'Modifica los detalles de tu noticia' : 'Comparte una nueva historia con el mundo' ?>
            </p>
        </div>

        <!-- Formulario Principal -->
        <div class="glass-card rounded-2xl p-8">
            
            <!-- Alertas -->
            <?php if (!empty($errores['general'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <div class="flex items-center">
                        <span class="text-red-500 mr-3">⚠️</span>
                        <span><?= htmlspecialchars($errores['general']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($errores['imagenes'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <div class="flex items-center">
                        <span class="text-red-500 mr-3">⚠️</span>
                        <span><?= htmlspecialchars($errores['imagenes']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form action="gestionar_noticia.php<?= $modo_edicion ? '?id=' . $noticia['id'] : '' ?>" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="space-y-6"
                  x-data="formHandler()">
                
                <?php if ($modo_edicion): ?>
                    <input type="hidden" name="id" value="<?= $noticia['id'] ?>">
                <?php endif; ?>

                <!-- Título -->
                <div>
                    <input type="text" 
                           id="titulo" 
                           name="titulo" 
                           value="<?= htmlspecialchars($noticia['titulo']) ?>" 
                           class="input-field w-full px-4 py-4 rounded-xl text-lg font-medium <?= isset($errores['titulo']) ? 'border-red-300' : '' ?>"
                           placeholder="Título de la noticia"
                           required>
                    <?php if (isset($errores['titulo'])): ?>
                        <p class="mt-2 text-red-600 text-sm"><?= $errores['titulo'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Autor y Categoría -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <input type="text" 
                               id="autor" 
                               name="autor" 
                               value="<?= htmlspecialchars($noticia['autor']) ?>" 
                               class="input-field w-full px-4 py-4 rounded-xl <?= isset($errores['autor']) ? 'border-red-300' : '' ?>"
                               placeholder="Nombre del autor"
                               required>
                        <?php if (isset($errores['autor'])): ?>
                            <p class="mt-2 text-red-600 text-sm"><?= $errores['autor'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <select id="id_categoria" 
                                name="id_categoria" 
                                class="input-field w-full px-4 py-4 rounded-xl <?= isset($errores['categoria']) ? 'border-red-300' : '' ?>"
                                required>
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($noticia['id_categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['categoria'])): ?>
                            <p class="mt-2 text-red-600 text-sm"><?= $errores['categoria'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen -->
                <div>
                    <div class="relative">
                        <textarea id="resumen" 
                                  name="resumen" 
                                  rows="3" 
                                  maxlength="250"
                                  x-on:input="resumenCount = $event.target.value.length"
                                  class="input-field w-full px-4 py-4 rounded-xl resize-none <?= isset($errores['resumen']) ? 'border-red-300' : '' ?>"
                                  placeholder="Resumen de la noticia (opcional)"><?= htmlspecialchars($noticia['resumen'] ?? '') ?></textarea>
                        <div class="absolute bottom-3 right-3 text-sm text-gray-400" 
                             :class="resumenCount > maxResumen ? 'text-red-500' : resumenCount > maxResumen * 0.8 ? 'text-amber-500' : 'text-gray-400'">
                            <span x-text="resumenCount"></span>/250
                        </div>
                    </div>
                    <?php if (isset($errores['resumen'])): ?>
                        <p class="mt-2 text-red-600 text-sm"><?= $errores['resumen'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Contenido -->
                <div>
                    <textarea id="contenido" 
                              name="contenido" 
                              rows="8" 
                              class="input-field w-full px-4 py-4 rounded-xl resize-y <?= isset($errores['contenido']) ? 'border-red-300' : '' ?>"
                              placeholder="Contenido completo de la noticia"
                              required><?= htmlspecialchars($noticia['contenido']) ?></textarea>
                    <?php if (isset($errores['contenido'])): ?>
                        <p class="mt-2 text-red-600 text-sm"><?= $errores['contenido'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Sección de Imágenes -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700 border-b border-gray-200 pb-2">
                        📸 Imágenes
                    </h3>

                    <!-- Imagen Principal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">
                            Imagen Principal
                        </label>
                        <div class="relative">
                            <input type="file" 
                                   name="url_grande" 
                                   id="url_grande"
                                   accept="image/*" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   onchange="previewImage(this, 'preview_grande')">
                            
                            <div class="file-upload-area rounded-xl p-8 text-center min-h-[120px] flex items-center justify-center">
                                <div id="preview_grande">
                                    <?php if ($modo_edicion && !empty($imagenes_noticia) && !empty($imagenes_noticia[0]['url_grande'])): ?>
                                        <div class="text-green-600">
                                            <img src="/News-System/public/uploads/noticias/<?= htmlspecialchars($imagenes_noticia[0]['url_grande']) ?>"
                                                 alt="Imagen actual"
                                                 class="mx-auto mb-2 rounded-xl max-h-32" />
                                            <div class="text-2xl mb-2">✅</div>
                                            <p class="text-sm font-medium">Imagen actual: <?= htmlspecialchars($imagenes_noticia[0]['url_grande']) ?></p>
                                            <p class="text-xs text-gray-500">Selecciona una nueva para reemplazar</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-gray-400">
                                            <div class="text-3xl mb-2">🖼️</div>
                                            <p class="text-sm">Haz clic para seleccionar imagen principal</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thumbnails -->
                    <div class="grid md:grid-cols-3 gap-4">
                        <!-- Thumbnail Principal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">
                                Thumbnail Principal
                            </label>
                            <div class="relative">
                                <input type="file" 
                                       name="url_thumbnail" 
                                       id="url_thumbnail"
                                       accept="image/*" 
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                       onchange="previewImage(this, 'preview_thumbnail')">
                                
                                <div class="file-upload-area rounded-xl p-4 text-center h-24 flex items-center justify-center">
                                    <div id="preview_thumbnail" class="text-xs">
                                        <?php if ($modo_edicion && !empty($imagenes_noticia) && !empty($imagenes_noticia[0]['url_thumbnail'])): ?>
                                            <div class="text-green-600">
                                                <div>✅</div>
                                                <div class="mt-1">Actual</div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-gray-400">📷 Seleccionar</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thumbnail 1 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">
                                Thumbnail 1
                            </label>
                            <div class="relative">
                                <input type="file" 
                                       name="url_thumbnail_1" 
                                       id="url_thumbnail_1"
                                       accept="image/*" 
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                       onchange="previewImage(this, 'preview_thumbnail_1')">
                                
                                <div class="file-upload-area rounded-xl p-4 text-center h-24 flex items-center justify-center">
                                    <div id="preview_thumbnail_1" class="text-xs">
                                        <?php if ($modo_edicion && !empty($imagenes_noticia) && !empty($imagenes_noticia[0]['url_thumbnail_1'])): ?>
                                            <div class="text-green-600">✅ Actual</div>
                                        <?php else: ?>
                                            <div class="text-gray-400">📷 Seleccionar</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thumbnail 2 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">
                                Thumbnail 2
                            </label>
                            <div class="relative">
                                <input type="file" 
                                       name="url_thumbnail_2" 
                                       id="url_thumbnail_2"
                                       accept="image/*" 
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                       onchange="previewImage(this, 'preview_thumbnail_2')">
                                
                                <div class="file-upload-area rounded-xl p-4 text-center h-24 flex items-center justify-center">
                                    <div id="preview_thumbnail_2" class="text-xs">
                                        <?php if ($modo_edicion && !empty($imagenes_noticia) && !empty($imagenes_noticia[0]['url_thumbnail_2'])): ?>
                                            <div class="text-green-600">✅ Actual</div>
                                        <?php else: ?>
                                            <div class="text-gray-400">📷 Seleccionar</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opción de publicación -->
                <?php if (!$es_editor): ?>
<!-- Opción de publicación -->
<div class="bg-blue-50 rounded-xl p-4">
    <label class="flex items-center cursor-pointer">
        <input type="checkbox" 
               name="publicar_ahora" 
               class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500"
               x-model="publishNow"
               checked>
        <span class="ml-3 text-sm font-medium text-gray-700">
            🚀 Publicar inmediatamente
        </span>
    </label>
</div>
<?php else: ?>
<!-- Mensaje para editores -->
<div class="bg-yellow-50 rounded-xl p-4">
    <div class="flex items-center">
        <span class="text-yellow-500 mr-3">📝</span>
        <span class="text-sm font-medium text-gray-700">
            La noticia se guardará como borrador para revisión
        </span>
    </div>
</div>
<?php endif; ?>

                <!-- Botones -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" 
                            class="btn-primary flex-1 text-white font-semibold py-4 px-6 rounded-xl">
                        <?= $modo_edicion ? 'Actualizar Noticia' : 'Crear Noticia' ?>
                    </button>
                    
                    <a href="indexnoticia.php" 
                       class="flex-none bg-gray-500 hover:bg-gray-600 text-white font-semibold py-4 px-6 rounded-xl transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>

            <!-- Link de vuelta -->
            <div class="text-center mt-6">
                <a href="indexnoticia.php" class="text-gray-600 hover:text-gray-800 text-sm">
                    ← Volver al inicio
                </a>
            </div>
        </div>
    </div>
    <script>
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            
            function formHandler() {
                return {
                    publishNow: true,
                    resumenCount: <?= strlen($noticia['resumen'] ?? '') ?>,
                    maxResumen: 250
                }
            }

            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                
                if (!preview) {
                    console.error(`Elemento con ID '${previewId}' no encontrado`);
                    return;
                }
                
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    
                    // Validar tipo de archivo
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Por favor selecciona solo archivos de imagen (JPG, PNG, GIF, WebP)');
                        input.value = '';
                        return;
                    }
                    
                    // Validar tamaño (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('El archivo es demasiado grande. El tamaño máximo es 5MB.');
                        input.value = '';
                        return;
                    }
                    
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    const fileName = file.name.length > 25 ? file.name.substring(0, 25) + '...' : file.name;
                    
                    if (previewId === 'preview_grande') {
                        preview.innerHTML = `
                            <div class="text-green-600">
                                <div class="text-2xl mb-2">✅</div>
                                <p class="text-sm font-medium">${fileName}</p>
                                <p class="text-xs text-gray-500">${fileSize} MB</p>
                            </div>
                        `;
                    } else {
                        preview.innerHTML = `
                            <div class="text-green-600 text-xs">
                                <div>✅</div>
                                <div class="mt-1">${fileName.substring(0, 15)}${fileName.length > 15 ? '...' : ''}</div>
                            </div>
                        `;
                    }
                }
            }
            
            // Hacer la función previewImage global para que funcione con onclick
            window.previewImage = previewImage;
            
            // DEBUG: Monitorear el envío del formulario con verificación de existencia
            const formulario = document.querySelector('form');
            if (formulario) {
                formulario.addEventListener('submit', function(e) {
                    console.log('=== ENVÍO DE FORMULARIO ===');
                    
                    const formData = new FormData(this);
                    console.log('Datos del formulario:');
                    
                    for (let [key, value] of formData.entries()) {
                        if (value instanceof File) {
                            console.log(`${key}: ${value.name} (${value.size} bytes)`);
                        } else {
                            console.log(`${key}: ${value}`);
                        }
                    }
                    
                    // Verificar campos de imagen específicamente
                    const camposImagen = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
                    camposImagen.forEach(campo => {
                        const input = document.querySelector(`input[name="${campo}"]`);
                        if (input && input.files && input.files[0]) {
                            console.log(`✅ ${campo}: ${input.files[0].name} seleccionado`);
                        } else {
                            console.log(`❌ ${campo}: No hay archivo seleccionado`);
                        }
                    });
                });
            } else {
                console.error('No se encontró el formulario en la página');
            }
            
            // Verificar que todos los elementos necesarios existen
            console.log('=== VERIFICACIÓN DE ELEMENTOS DOM ===');
            const elementosEsperados = [
                'preview_grande',
                'preview_thumbnail', 
                'preview_thumbnail_1',
                'preview_thumbnail_2',
                'url_grande',
                'url_thumbnail',
                'url_thumbnail_1', 
                'url_thumbnail_2'
            ];
            
            elementosEsperados.forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    console.log(`✅ Elemento '${id}' encontrado`);
                } else {
                    console.error(`❌ Elemento '${id}' NO encontrado`);
                }
            });
        });
    </script>
</body>
</html><?php
if (!isset($imagenes_noticia) || !is_array($imagenes_noticia)) {
    $imagenes_noticia = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = validarImagenesRequeridas($modo_edicion, $imagenes_noticia);
    // ...otras validaciones y lógica...
}
?>