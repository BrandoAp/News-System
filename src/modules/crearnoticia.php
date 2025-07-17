<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CreacionNoticia</title>
</head>
<body>
    <div class="container mt-4">
    <h1>Crear Nueva Noticia</h1>
    
    <form action="guardar.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control" id="titulo" name="titulo" required>
        </div>
        
        <div class="mb-3">
            <label for="contenido" class="form-label">Contenido</label>
            <textarea class="form-control" id="contenido" name="contenido" rows="5" required></textarea>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="autor" class="form-label">Autor</label>
                <input type="text" class="form-control" id="autor" name="autor" required>
            </div>
            <div class="col-md-6">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="imagenes" class="form-label">Imágenes</label>
            <input type="file" class="form-control" id="imagenes" name="imagenes[]" multiple>
            <div class="form-text">Seleccione una o más imágenes. La primera será la principal.</div>
        </div>
        
        <div class="mb-3">
            <label for="video" class="form-label">Video (URL)</label>
            <input type="url" class="form-control" id="video" name="video">
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar Noticia</button>
        <a href="../../public/indexnoticia.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>