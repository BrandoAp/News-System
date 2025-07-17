<?php
    require_once __DIR__ . '/../../db/DatabaseManager.php';  

    class Noticias{
            private $dbManager;
            private $table_name = "noticias";
            public $id,$titulo,$autor,$id_usuario_creador,$resumen,$contenido,$id_categoria,$id_estado,$creado_en,$publicado_en,$actualizado_en;
           public function __construct(DatabaseManager $dbManager)
                {
                    $this->dbManager = $dbManager;
                }

            public function listarNoticias(): array
                {
                    return $this->dbManager->select($this->table_name, '*');
                }
            
            public function crearNoticia(): bool
                {
                    $data = [
                        'titulo' => $this->titulo,
                        'autor' => $this->autor,
                        'id_usuario_creador' => $this->id_usuario_creador,
                        'resumen' => $this->resumen,
                        'contenido' => $this->contenido,
                        'id_categoria' => $this->id_categoria
                    ];
                    return $this->dbManager->insertSeguro($this->table_name, $data);
                }
            
            public function actualizarNoticia(): bool
                {
                    $data = [
                        'titulo' => $this->titulo,
                        'autor' => $this->autor,
                        'resumen' => $this->resumen,
                        'contenido' => $this->contenido,
                        'id_categoria' => $this->id_categoria,
                        'id_estado' => $this->id_estado
                    ];
                    $conditions = ['id' => $this->id];
                    return $this->dbManager->updateSeguro($this->table_name, $data, $conditions);
                }

            
    public function buscarPorId(int $id): bool
    {
        $result = $this->dbManager->select($this->table_name, '*', ['id' => $id]);
        if (!empty($result)) {
            $noticiaData = $result[0];
            foreach ($noticiaData as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        return false;
    }
}