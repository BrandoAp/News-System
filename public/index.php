<?php

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News System</title>
    <link rel="stylesheet" href="../src/output.css">
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-start">
    <div class="w-full max-w-5xl mt-8 mb-10">
        <div class="bg-gray-50 rounded-3xl shadow-lg px-8 py-6 flex flex-col items-center">
            <h1 class="text-3xl md:text-4xl text-blue-900 font-bold mb-2 text-center">Portal de Noticias</h1>
            <span class="text-gray-600 text-center">Mantente informado con las últimas noticias y acontecimientos</span>
        </div>
        <div class="flex justify-between mt-8 px-8">
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1">127</h4>
                <span class="text-gray-600 text-sm">Noticias Publicadas</span>
            </div>
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1">1235</h4>
                <span class="text-gray-600 text-sm">Visitas Hoy</span>
            </div>
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1">44</h4>
                <span class="text-gray-600 text-sm">Usuarios Activos</span>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-6 grid-rows-6 gap-4 w-full max-w-5xl mb-10">
        <!-- Noticia principal -->
        <div class="col-span-4 row-span-6 bg-gradient-to-tr from-indigo-400 via-blue-400 to-purple-400 rounded-3xl shadow-lg flex flex-col justify-end p-8 relative overflow-hidden">
            <div class="absolute top-6 left-6 text-white text-sm font-medium opacity-90">
                <span class="bg-white/20 px-3 py-1 rounded-lg">📰 Noticia Principal</span>
            </div>
            <div class="mt-auto">
                <h2 class="text-2xl font-bold text-white mb-2">Importante actualización del sistema educativo nacional
                </h2>
                <p class="text-white/90 mb-4">El Ministerio de Educación anuncia nuevas medidas para mejorar la calidad
                    educativa en todo el país. Las reformas incluyen modernización de infraestructuras, capacitación docente
                    y nuevos programas académicos...</p>
                <div class="flex items-center justify-between text-white/80 text-sm">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                        </svg>
                        6 de Julio, 2025
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 0 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                        </svg>
                        2,456 visitas
                    </span>
                </div>
            </div>
        </div>
        <!-- Noticias secundarias -->
        <div class="col-span-2 row-span-2 col-start-5 bg-white rounded-2xl shadow p-5 flex flex-col justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 mb-1">Nuevas tecnologías en el sector salud</h3>
                <p class="text-gray-600 text-sm mb-2">Implementación de sistemas digitales para mejorar la atención
                    médica...</p>
            </div>
            <span class="text-xs text-gray-400">5 de Julio, 2025</span>
        </div>
        <div class="col-span-2 row-span-2 col-start-5 row-start-3 bg-white rounded-2xl shadow p-5 flex flex-col justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 mb-1">Desarrollo económico regional</h3>
                <p class="text-gray-600 text-sm mb-2">Proyectos de inversión que impulsarán el crecimiento económico...
                </p>
            </div>
            <span class="text-xs text-gray-400">4 de Julio, 2025</span>
        </div>
        <div class="col-span-2 row-span-2 col-start-5 row-start-5 bg-white rounded-2xl shadow p-5 flex flex-col justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 mb-1">Programa de becas estudiantiles</h3>
                <p class="text-gray-600 text-sm mb-2">Apertura de convocatoria para becas de educación superior...</p>
            </div>
            <span class="text-xs text-gray-400">1 de Julio, 2025</span>
        </div>
    </div>
</body>
</html>