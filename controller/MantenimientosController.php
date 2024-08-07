<?php
//autor: Vélez Pulido Christopher Jeremy
require_once 'model/dao/MantenimientosDAO.php';
require_once 'model/dto/Mantenimiento.php';
require_once 'model/dao/PropiedadesDAO.php';
require_once 'model/dto/Propiedad.php';

class MantenimientosController {
    private $model;

    public function __construct() {
        $this->model = new MantenimientoDAO();
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    private function checkRole() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
            error_log("Acceso denegado. Rol de sesión: " . (isset($_SESSION['rol']) ? $_SESSION['rol'] : 'no definido'));

            header('Location: index.php?op=cerrar&num=12');
            exit();
        }
    }

    public function search() {
        $this->checkRole(); 
        // Leer el parámetro de búsqueda
        $parametro = isset($_POST['b']) ? htmlentities($_POST['b']) : '';
        
        // Comunica con el modelo para obtener los resultados
        $resultados = $this->model->selectAll($parametro);
        
        // Configura el título y carga la vista
        $titulo = "Buscar Mantenimientos";
        require_once VMANTE . 'list.php';
    }
    

    public function index() {
        $this->checkRole(); 

        $resultados = $this->model->selectAll();
        $titulo = "Lista de mantenimientos";
        require_once VMANTE . 'list.php';
    }

    public function view_new() {
        $this->checkRole(); // Verificar rol antes de continuar
        $titulo = "Nuevo mantenimiento";
        $propiedadesDAO = new PropiedadesDAO();
        $propiedades = $propiedadesDAO->selectAll();

        

        require_once VMANTE . 'new.php';
    }

    public function view_edit() {
        $this->checkRole(); // Verificar rol antes de continuar

        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $mantenimiento = $this->model->selectOne($id);

        if ($mantenimiento) {
            $propiedadesDAO = new PropiedadesDAO();
            $propiedades = $propiedadesDAO->selectAll();
            $titulo = "Editar mantenimiento";
            require_once VMANTE . 'edit.php';
        } else {
            echo "Mantenimiento no encontrado.";
        }
    }

    public function view() {
        $this->checkRole(); // Verificar rol antes de continuar

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $mantenimiento = $this->model->selectOne($id);


        if ($mantenimiento) {
            $titulo = "Detalles de mantenimiento";
            require_once VMANTE . 'view.php';
        } else {
            echo "Mantenimiento no encontrado.";
        }
    }

    public function create() {
        $this->checkRole(); // Verificar rol antes de continuar

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            $costo = $_POST['costo'];

       

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                $msj = "La fecha de inicio debe ser anterior a la fecha de fin.";
            } 
            elseif (!is_numeric($costo) || $costo < 0) {
                $msj = "El costo no puede ser un número negativo.";
            }
            else {
                $mantenimiento = new Mantenimiento();
                
                $mantenimiento->setIdPropiedad(htmlentities($_POST['id_propiedad']));
                $mantenimiento->setFechaInicio(htmlentities($fecha_inicio));
                $mantenimiento->setFechaFin(htmlentities($fecha_fin));
                $mantenimiento->setDescripcion(htmlentities($_POST['descripcion']));
                $mantenimiento->setNombreMantenimiento(htmlentities($_POST['nombre_mantenimiento']));
                $mantenimiento->setEncargado(htmlentities($_POST['encargado']));
                $mantenimiento->setEstado(htmlentities($_POST['estado']));
                $mantenimiento->setCosto(htmlentities($costo)); 
                
                if ($this->model->insert($mantenimiento)) {
                    $msj = 'Mantenimiento creado exitosamente';
                } else {
                    $msj = 'No se pudo crear el mantenimiento.';
                }
            }
            $_SESSION['mensaje'] = $msj;
            header('Location: index.php?c=Mantenimientos&f=index');
            exit(); 
        } 
    }

    public function edit() {
        $this->checkRole(); // Verificar rol antes de continuar

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            $costo = $_POST['costo'];

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                $msj = "La fecha de inicio debe ser anterior a la fecha de fin.";
            } 
            elseif (!is_numeric($costo) || $costo < 0) {
                $msj = "El costo no puede ser un número negativo.";
            }
            else {
                $mantenimiento = new Mantenimiento();
                $mantenimiento->setId($id);
                    $mantenimiento->setIdPropiedad(htmlentities($_POST['id_propiedad']));
                    $mantenimiento->setFechaInicio($fecha_inicio);
                    $mantenimiento->setFechaFin($fecha_fin);
                    $mantenimiento->setDescripcion(htmlentities($_POST['descripcion']));
                    $mantenimiento->setNombreMantenimiento(htmlentities($_POST['nombre_mantenimiento']));
                    $mantenimiento->setEncargado(htmlentities($_POST['encargado']));
                    $mantenimiento->setEstado(htmlentities($_POST['estado']));
                    $mantenimiento->setCosto($costo);
                    
                    if ($this->model->update($mantenimiento)) {
                        $msj = 'Mantenimiento actualizado exitosamente';
                    } else {
                        $msj = 'No se pudo actualizar el mantenimiento.';
                    }
                 
            }

            // Guardar el mensaje en la sesión y redirigir
            $_SESSION['mensaje'] = $msj;
            header('Location: index.php?c=mantenimientos&f=index');
            exit(); // Asegurarse de que el script se detenga después de redirigir
        } 
    }

    public function delete() {
        $this->checkRole(); // Verificar rol antes de continuar

        $man = new Mantenimiento();
        $man->setId(htmlentities($_REQUEST['id']));
        $exito = $this->model->delete($man);
        $msj = 'Mantenimiento eliminado exitosamente';
        if (!$exito) {
            $msj = "No se pudo eliminar el mantenimiento";
        }

       

        $_SESSION['mensaje'] = $msj;
        header('Location: index.php?c=Mantenimientos&f=index');
        exit(); // Asegurarse de que el script se detenga después de redirigir
    }
}
?>
