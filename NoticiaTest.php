<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../logica/noticia.php';

class NoticiaTest extends TestCase
{
    // ==========================
    // TESTS QUE YA TENÍAS
    // ==========================

    public function testConstanteBorrador()
    {
        $this->assertEquals(
            "BORRADOR",
            noticia::BORRADOR
        );
    }

    public function testConstantePublicada()
    {
        $this->assertEquals(
            "PUBLICADA",
            noticia::PUBLICADA
        );
    }

    public function testCrearObjetoNoticia()
    {
        $noticia = new noticia();

        $noticia->titulo = "Mi noticia de prueba";
        $noticia->descripcion = str_repeat("a", 60);

        $this->assertEquals(
            "Mi noticia de prueba",
            $noticia->titulo
        );

        $this->assertNotEmpty(
            $noticia->descripcion
        );
    }

   
    // ================ ejercicio 1
  

    public function testTituloCumpleLongitud()
    {
        $titulo = "Noticia institucional";

        $this->assertGreaterThanOrEqual(
            10,
            strlen($titulo)
        );

        $this->assertLessThanOrEqual(
            100,
            strlen($titulo)
        );
    }

    public function testDescripcionTieneMinimo50Caracteres()
    {
        $descripcion = str_repeat("a", 60);

        $this->assertGreaterThanOrEqual(
            50,
            strlen($descripcion)
        );
    }

    public function testCambioEstadoBorradorAValidacion()
    {
        $noticia = new noticia();

        $noticia->estado = noticia::BORRADOR;

        $nuevoEstado = noticia::VALIDACION;

        $this->assertTrue(
            $noticia->estado == noticia::BORRADOR &&
            $nuevoEstado == noticia::VALIDACION
        );
    }

    /*public function testNoPermitirTituloDuplicadoPublicado()
    {
        require_once __DIR__ . '/../config/database.php';

        $conn = Database::conectar();

        $titulo = "Título repetido";

        $stmt = $conn->prepare("
            SELECT id
            FROM noticias
            WHERE titulo = ?
            AND estado = 'PUBLICADA'
        ");

        $stmt->bind_param("s", $titulo);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $this->assertGreaterThan(
            0,
            $resultado->num_rows
        );
    }*/

    public function testNoPermitirTituloDuplicadoPublicado()
    {
        require_once __DIR__ . '/../config/database_tests.php';

        $conn = DatabaseTest::conectar();

        
        $conn->query("DELETE FROM noticias");

        $stmt = $conn->prepare("INSERT INTO noticias (titulo, descripcion, estado) VALUES (?, ?, ?)");
        $titulo      = "Noticia institucional de prueba";
        $descripcion = str_repeat("a", 60);
        $estado      = "PUBLICADA";

        $stmt->bind_param("sss", $titulo, $descripcion, $estado);
        $stmt->execute();
        $stmt->close();

        
        $stmt2 = $conn->prepare("SELECT id FROM noticias WHERE titulo = ? AND estado = 'PUBLICADA'");
        $stmt2->bind_param("s", $titulo);
        $stmt2->execute();
        $resultado = $stmt2->get_result();

        
        $this->assertEquals(1,$resultado->num_rows,
            "Debe existir exactamente una noticia publicada con ese título"
        );

        
        $this->assertGreaterThan(0,$resultado->num_rows,
            "Ya existe una noticia PUBLICADA con ese título: no se puede repetir"
        );

        $stmt2->close();
    }
    


    // ---------- TÍTULO ----------

    // 1: 9 caracteres → inválido
    public function testTituloPermite9Caracteres()
    {
        $titulo = str_repeat("a", 9);
        $this->assertFalse(
            $this->validarTitulo($titulo),
            "Un título de 9 caracteres NO debe ser permitido"
        );
    }

    // 2: 10 caracteres → válido
    public function testTituloPermite10Caracteres()
    {
        $titulo = str_repeat("a", 10);
        $this->assertTrue(
            $this->validarTitulo($titulo),
            "Un título de 10 caracteres SÍ debe ser permitido"
        );
    }

    // 3: 11 caracteres → válido
    public function testTituloPermite11Caracteres()
    {
        $titulo = str_repeat("a", 11);
        $this->assertTrue(
            $this->validarTitulo($titulo),
            "Un título de 11 caracteres SÍ debe ser permitido"
        );
    }

    // 4: 50 caracteres → válido
    public function testTituloPermite50Caracteres()
    {
        $titulo = str_repeat("a", 50);
        $this->assertTrue(
            $this->validarTitulo($titulo),
            "Un título de 50 caracteres SÍ debe ser permitido"
        );
    }

    // 5: 99 caracteres → válido
    public function testTituloPermite99Caracteres()
    {
        $titulo = str_repeat("a", 99);
        $this->assertTrue(
            $this->validarTitulo($titulo),
            "Un título de 99 caracteres SÍ debe ser permitido"
        );
    }

    // 6: 100 caracteres → válido
    public function testTituloPermite100Caracteres()
    {
        $titulo = str_repeat("a", 100);
        $this->assertTrue(
            $this->validarTitulo($titulo),
            "Un título de 100 caracteres SÍ debe ser permitido"
        );
    }

    // 7: 101 caracteres → inválido 
    public function testTituloPermite101Caracteres()
    {
        $titulo = str_repeat("a", 101);
        $this->assertFalse(
            $this->validarTitulo($titulo),
            "Un título de 101 caracteres NO debe ser permitido"
        );
    }
    
    // ---------- DESCRIPCIÓN ----------

    // 1: 49 caracteres → inválido
    public function testDescripcionPermite49Caracteres()
    {
        $descripcion = str_repeat("a", 49);
        $this->assertFalse(
            $this->validarDescripcion($descripcion),
            "Una descripción de 49 caracteres NO debe ser permitida"
        );
    }

    // 2: 50 caracteres → válido
    public function testDescripcionPermite50Caracteres()
    {
        $descripcion = str_repeat("a", 50);
        $this->assertTrue(
            $this->validarDescripcion($descripcion),
            "Una descripción de 50 caracteres SÍ debe ser permitida"
        );
    }

    // ---------- IMAGEN ---------- (máximo 2MB = 2097152 bytes) ----------

    // I1: 1.9 MB → válido
    public function testImagenI1UnoPuntoNueveMBValida()
    {
        $bytes = (int)(1.9 * 1024 * 1024); // 1992294 bytes
        $this->assertTrue(
            $this->validarTamanioImagen($bytes),
            "I1: 1.9 MB debe ser aceptada"
        );
    }

    // I2: exactamente 2.0 MB → válido (límite exacto)
    public function testImagenI2DosMBExactoValida()
    {
        $bytes = 2 * 1024 * 1024; // 2097152 bytes
        $this->assertTrue(
            $this->validarTamanioImagen($bytes),
            "I2: exactamente 2 MB debe ser aceptada"
        );
    }

    // I3: 2.1 MB → inválido
    public function testImagenI3DosPuntoUnMBInvalida()
    {
        $bytes = (int)(2.1 * 1024 * 1024); // 2202009 bytes
        $this->assertFalse(
            $this->validarTamanioImagen($bytes),
            "I3: 2.1 MB debe ser rechazada"
        );
    }

    // =====================================================
    // HELPERS PRIVADOS
    // =====================================================

    private function validarTitulo(string $titulo): bool
    {
        $len = strlen($titulo);
        return $len >= 10 && $len <= 100;
    }

    private function validarDescripcion(string $descripcion): bool
    {
        return strlen($descripcion) >= 50;
    }

    private function validarTamanioImagen(int $bytes): bool
    {
        return $bytes <= 2 * 1024 * 1024;
    }

    
}
