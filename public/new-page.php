<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Noticia</title>
    <link rel="stylesheet" href="./css/noticias.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <!-- Header -->
    <div class="w-full max-w-5xl mt-8">
        <div class="bg-gray-50 rounded-3xl shadow-lg px-8 py-6 flex flex-col items-center">
            <h1 class="text-3xl md:text-4xl text-blue-900 font-bold mb-2 text-center">Portal de Noticias</h1>
            <span class="text-gray-600 text-center">Mantente informado con las últimas noticias y acontecimientos</span>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="w-full max-w-5xl mt-8">
        <div class="bg-white rounded-2xl shadow p-8">
            <!-- Volver -->
            <a href="index.php" class="inline-flex items-center px-4 py-1 mb-4 rounded-full bg-gray-100 text-blue-700 text-sm font-medium hover:bg-gray-200 transition">
                &#8592; Volver a Noticias
            </a>
            <!-- Título y meta -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Importante actualización del sistema educativo nacional</h2>
            <div class="flex flex-wrap items-center gap-4 text-gray-500 text-sm mb-4">
                <span class="flex items-center gap-1">
                    <!-- Icono calendario -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    6 de Julio, 2025
                </span>
                <span class="flex items-center gap-1">
                    <!-- Icono vistas -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 0 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
                    </svg>
                    2,451 vistas
                </span>
                <span class="flex items-center gap-1">
                    <!-- Icono usuario -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 8-4 8-4s8 0 8 4" />
                    </svg>
                    Mati González
                </span>
            </div>
            <!-- Imagen principal -->
            <div class="bg-gradient-to-tr from-indigo-400 via-blue-400 to-purple-400 rounded-xl flex items-center justify-center h-56 mb-6 relative overflow-hidden">
                <span class="text-white font-medium text-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <path d="M21 15l-5-5L5 21" />
                    </svg>
                    Imagen de la Noticia
                </span>
            </div>
            <!-- Texto de la noticia -->
            <p class="text-gray-700 mb-6">
                El Ministerio de Educación ha anunciado una serie de reformas estructurales que transformarán el panorama educativo del país. Estas medidas, de acuerdo con fuentes cercanas al proceso del plan, están destinadas a elevar la calidad de la educación mediante la modernización de infraestructuras, la capacitación docente y la implementación de nuevos programas académicos...
            </p>
            <p class="text-gray-700 mb-6">
                Las reformas también contemplan la reducción de la brecha digital en las regiones, así como la colaboración con el sector privado para la integración de nuevas tecnologías en las aulas de todo el país.
            </p>
            <p class="text-gray-700 mb-6">
                Se espera que los cambios comiencen a implementarse a partir del próximo semestre, beneficiando a más de 2 millones de estudiantes y capacitando a más de 50,000 maestros en todo el territorio nacional.
            </p>
            <!-- Acciones -->
            <div class="flex gap-3 mb-6">
                <button class="px-4 py-2 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 transition text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 9V5a3 3 0 0 0-6 0v4" />
                        <path d="M5 12h14" />
                        <path d="M12 17v-5" />
                    </svg>
                    Me gusta
                </button>
                <button class="px-4 py-2 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 transition text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z" />
                    </svg>
                    Compartir
                </button>
                <button class="px-4 py-2 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 transition text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M8 15h8M8 11h8M8 7h8" />
                    </svg>
                    Guardar
                </button>
            </div>
            <!-- Comentarios -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Comentarios</h3>
                <form class="mb-6">
                    <textarea class="w-full border border-gray-300 rounded-lg p-3 resize-none focus:outline-none focus:ring-2 focus:ring-blue-300" rows="2" placeholder="Escribe tu comentario..."></textarea>
                    <button type="submit" class="mt-2 px-6 py-2 bg-blue-600 text-white rounded-full font-medium hover:bg-blue-700 transition">Publicar Comentario</button>
                </form>
                <!-- Comentario ejemplo -->
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">GA</div>
                    <div>
                        <div class="bg-gray-100 rounded-lg px-4 py-2">
                            <span class="font-semibold text-gray-800">Gonzalo Andrade</span>
                            <p class="text-gray-700 text-sm mt-1">Excelente noticia, era necesario modernizar el sistema educativo.</p>
                        </div>
                        <span class="text-xs text-gray-400 ml-2">Hace 1 hora</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

