<?php
class Persona {
    protected $id;
    protected $nombre;
    protected $apellido;
    protected $correo;
    protected $clave; // plano solo si lo necesitas temporalmente

    public function __construct($id="", $nombre="", $apellido="", $correo="", $clave="") {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->clave = $clave;
    }
}
