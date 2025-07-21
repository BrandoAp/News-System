<?php
require_once __DIR__ . '/../src/controllers/configdetallenoticia.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?></title>
    <link rel="stylesheet" href="./css/noticias.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">

    <div class="w-full max-w-5xl mt-8">
        <div class="bg-white rounded-2xl shadow p-8">
            <!-- Volver -->
            <a href="indexnoticia.php" class="inline-flex items-center px-4 py-1 mb-4 rounded-full bg-gray-100 text-blue-700 text-sm font-medium hover:bg-gray-200 transition">
                &#8592; Volver a Noticias
            </a>
            
            <!-- Mensajes -->
            <?php if (!empty($mensaje)): ?>
                <div class="<?= strpos($mensaje, 'Error') !== false ? 'mensaje-error' : 'mensaje-exito' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            
            <!-- Título y meta -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($noticia['titulo']) ?></h2>
            <div class="flex flex-wrap items-center gap-4 text-gray-500 text-sm mb-4">
                <span class="flex items-center gap-1">
                    <!-- Icono calendario -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none" />
                        <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2" />
                        <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2" />
                        <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2" />
                    </svg>
                    <?= date('j \d\e F, Y', strtotime($noticia['publicado_en'])) ?>
                </span>
                <span class="flex items-center gap-1">
                    <!-- Icono usuario -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 8-4 8-4s8 0 8 4" />
                    </svg>
                    <?= htmlspecialchars($noticia['autor']) ?>
                </span>
            </div>
            
            <!-- Imagen principal -->
            <?php
            // Imagen principal (url_grande)
            if (!empty($imagenes) && !empty($imagenes[0]['url_grande'])): ?>
                <div class="bg-gradient-to-tr from-indigo-400 via-blue-400 to-purple-400 rounded-xl flex items-center justify-center h-[320px] md:h-[380px] mb-8 relative overflow-hidden">
                    <img src="/News-System/public/uploads/noticias/<?= htmlspecialchars($imagenes[0]['url_grande']) ?>"
                         alt="Imagen de la noticia"
                         class="object-cover w-full h-full rounded-xl" />
                </div>
            <?php endif; ?>

            <!-- Imágenes secundarias -->
            <?php
            // Imágenes secundarias (thumbnails)
            $imagenesSecundarias = [];
            if (!empty($imagenes)) {
                foreach ($imagenes as $img) {
                    if (!empty($img['url_thumbnail'])) {
                        $imagenesSecundarias[] = $img['url_thumbnail'];
                    }
                    if (!empty($img['url_thumbnail_1'])) {
                        $imagenesSecundarias[] = $img['url_thumbnail_1'];
                    }
                    if (!empty($img['url_thumbnail_2'])) {
                        $imagenesSecundarias[] = $img['url_thumbnail_2'];
                    }
                }
            }
            ?>
            <?php if (!empty($imagenesSecundarias)): ?>
                <div class="w-full flex justify-center">
                    <div class="flex flex-row gap-4 mb-8 items-center h-40">
                        <?php foreach ($imagenesSecundarias as $urlImagen): ?>
                            <div class="flex items-center justify-center w-full h-full overflow-hidden rounded-xl shadow-md hover:shadow-xl transition duration-300 bg-white">
                                <img
                                    src="/News-System/public/uploads/noticias/<?= htmlspecialchars($urlImagen) ?>"
                                    alt="Imagen secundaria"
                                    class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105" />
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Texto de la noticia -->
            <p class="text-gray-700 mb-6">
                <?= nl2br(htmlspecialchars($noticia['contenido'])) ?>
            </p>
            
            <!-- Comentarios -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Comentarios</h3>
                
                <!-- Formulario para nuevo comentario (solo lectores) -->
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'lector'): ?>
                    <form class="mb-6" method="POST">
                        <textarea name="comentario" class="w-full border border-gray-300 rounded-lg p-3 resize-none focus:outline-none focus:ring-2 focus:ring-blue-300" rows="2" placeholder="Escribe tu comentario..."></textarea>
                        <?php if (!empty($comentarioError)): ?>
                            <p class="text-red-500 text-sm mt-2"><?= htmlspecialchars($comentarioError) ?></p>
                        <?php endif; ?>
                        <button type="submit" class="mt-2 px-6 py-2 bg-blue-600 text-white rounded-full font-medium hover:bg-blue-700 transition">Publicar Comentario</button>
                    </form>
                <?php elseif (!isset($_SESSION['usuario_rol'])): ?>
                    <div class="text-gray-600 text-sm mb-6">Debes iniciar sesión para comentar.</div>
                <?php elseif ($_SESSION['usuario_rol'] !== 'lector'): ?>
                    <div class="text-gray-600 text-sm mb-6">Solo los lectores pueden escribir comentarios.</div>
                <?php endif; ?>
                
            <!-- Comentarios existentes con respuestas -->
            <?php if (!empty($comentarios)): ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="flex items-start gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">
                            <?= strtoupper(substr(htmlspecialchars($comentario['nombre']), 0, 2)) ?>
                        </div>
                        <div class="flex-1">
                            <div class="bg-gray-100 rounded-lg px-4 py-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($comentario['nombre']) ?></span>
                                </div>
                                <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400 ml-2">
                                    Hace <?= isset($comentario['creado_en']) ? intval((time() - strtotime($comentario['creado_en'])) / 60) : '?' ?> minutos
                                </span>
                                <!-- botones de acciones -->

                                <div class="botones-comentario">
                                    <!-- Botón Responder (solo admin) -->
                                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                        <button class="btn-responder" onclick="toggleRespuestaForm(<?= $comentario['id'] ?>)">
                                            Responder
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Botón Eliminar (supervisor pueden eliminar) -->
                                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'supervisor'): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este comentario?');">
                                            <input type="hidden" name="eliminar_comentario" value="<?= $comentario['id'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <!-- Debug: Mostrar rol actual (eliminar después de verificar) -->
                                    <?php if (isset($_SESSION['usuario_rol'])): ?>
                                        <small style="color: gray; font-size: 10px;">
                                            (Rol actual: <?= htmlspecialchars($_SESSION['usuario_rol']) ?>)
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                                    
                            <!-- Formulario de respuesta (oculto por defecto, solo admin) -->
                            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                <div id="respuesta-form-<?= $comentario['id'] ?>" class="respuesta-form" style="display: none;">
                                    <form method="POST" class="mt-2">
                                        <input type="hidden" name="id_comentario_padre" value="<?= $comentario['id'] ?>">
                                        <textarea name="respuesta" class="w-full border border-gray-300 rounded-lg p-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-300" rows="2" placeholder="Escribe tu respuesta como administrador..."></textarea>
                                        <?php if (!empty($respuestaError)): ?>
                                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($respuestaError) ?></p>
                                        <?php endif; ?>
                                        <div class="flex gap-2 mt-1">
                                            <button type="submit" class="px-4 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">Responder</button>
                                            <button type="button" class="px-4 py-1 bg-gray-300 text-gray-700 rounded text-sm hover:bg-gray-400 transition" onclick="toggleRespuestaForm(<?= $comentario['id'] ?>)">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                                        
                            <!-- Respuestas al comentario -->
                            <?php if (!empty($comentario['respuestas'])): ?>
                                <div class="respuesta mt-4">
                                    <?php foreach ($comentario['respuestas'] as $respuesta): ?>
                                        <div class="flex items-start gap-3 mb-3">
                                            <div class="w-8 h-8 rounded-full bg-red-200 flex items-center justify-center font-bold text-red-700 text-xs">
                                            <?= strtoupper(substr(htmlspecialchars($respuesta['nombre']), 0, 2)) ?>
                                        </div>
                                        <div class="flex-1">
                                            <div class="bg-red-50 rounded-lg px-3 py-2">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($respuesta['nombre']) ?></span>
                                                    <span class="admin-badge">ADMIN</span>
                                                </div>
                                                <p class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($respuesta['contenido'])) ?></p>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-400 ml-2">
                                                    Hace <?= isset($respuesta['creado_en']) ? intval((time() - strtotime($respuesta['creado_en'])) / 60) : '?' ?> minutos
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-sm">No hay comentarios aún.</p>
        <?php endif; ?>
            </div>
        </div>
    </div>

   <script src="../src/js/gestionarnoticia.js"></script>
</body>
</html>