<form action="/public/indexnoticia.php" method="get" class="mb-4">
    <div class="input-group">
        <input type="text" class="form-control" name="busqueda" placeholder="Buscar noticias..." value="<?= htmlspecialchars($busqueda ?? '') ?>">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </div>
</form>