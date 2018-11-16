<?php
/**
 * generate by adconsole 0.1
 * @author: Indev Web www.dein.net.br
 * @mail team@indev.net.br
 */

class Indev extends TRecord
{
    const TABLENAME = 'Indev';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $cursos; 
	

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('nome'); 
	parent::addAttribute('cursos_id'); 
	
    }

    

    /**
* Method set_cursos
* Sample of usage: Indev->cursos = $object;
* @param $object Instance of cursosclass
*/
public function set_cursos(Cursos $object)
{
    $this->cursos = $object;
    $this->cursos_id = $object->id;
}

/**
 * Method get_cursos
 * Sample of usage: Indev->cursos->attribute;
 * @returns Cursos instance
 */
public function get_cursos()
{
    // loads the associated object
    if (empty($this->cursos))
        $this->cursos = new Cursos($this->cursos_id);

    // returns the associated object
    return $this->cursos;
} 
	

     

}