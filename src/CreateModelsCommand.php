<?php
/**
 * Created by PhpStorm.
 * User: Indev
 * Date: 16/11/18
 * Time: 09:52
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CreateModelsCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:model:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('create a new model')
            ->addArgument('name',null,'Name for Model')
          //  ->addArgument('fields',null,'List of fiends')
            ->addOption('fields','f',InputOption::VALUE_OPTIONAL,'add fields')
            ->addOption('primary-key','p',InputOption::VALUE_OPTIONAL,'primary key')
            ->addOption('assosiacao','s',InputOption::VALUE_OPTIONAL,'add assosiacao')
            ->addOption('composition','c',InputOption::VALUE_OPTIONAL,'add composition')
            ->addOption('aggregate','a',InputOption::VALUE_OPTIONAL,'add agreggation')
            ->addOption('pivot',null,InputOption::VALUE_OPTIONAL,'pivot record')
            ->addOption('idpolicy','i',InputOption::VALUE_OPTIONAL,'IDPOLICY')
            ->addOption('patch',null,InputOption::VALUE_OPTIONAL,'patch for model')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a model')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $fields = $input->getOption('fields');
        $key = $input->getOption('primary-key');
        $idpolicy = $input->getOption('idpolicy');
        $ipatch =  $input->getOption('patch');
        $assosiacao =  $input->getOption('assosiacao');
        $composition =  $input->getOption('composition');
        $aggregate = $input->getOption('aggregate');
        $pivot = $input->getOption('pivot');



        $pivot = (empty($pivot)?ucfirst($name).ucfirst($aggregate):$pivot);

        $key = (empty($key)?'id':$key);
        $idpolicy = (empty($idpolicy)?'max':$idpolicy);



        if(empty($ipatch))
            $ipatch = 'app/model';

        $out = $ipatch.'/'.$name.'.class.php';

        $patch = dirname( __FILE__,2);

        $patch = realpath($patch);


        $file = file_get_contents($patch.'/templates/models/HRecord.php');
        $file = str_replace('$name',$name,$file);
        $file = str_replace('$idpolicy',$idpolicy,$file);
        $file = str_replace('$key',$key,$file);



        $lines = ''; // atributos
        $asslines = ''; // associacao
        $comline = '';
        $linegetandset = '';
        $methodsFile = '';


// isere as associacoes



        $lines .= $this->addAttribute($fields);



// associacao
if(!empty($assosiacao)) {
    if ($this->strCharFind(',', $assosiacao)) {

        $assosiacao = is_array($assosiacao) ? $assosiacao : explode(',', $assosiacao);
        $lines .= $this->addAttribute($assosiacao, 'id');
        foreach ($assosiacao as $ass) {
            $asslines .= "private $ass; \n\t";

        }

    } else {
        $asslines .= "private $assosiacao; \n\t";
        $lines .= $this->addAttribute($assosiacao, 'id');
    }


    if (!empty($assosiacao)) {
        $assfile = file_get_contents($patch . '/templates/models/getset.php');
        $assosiacao = is_array($assosiacao) ? $assosiacao : explode(',', $assosiacao);

        // insere os get and Set
        if ($this->strCharFind(',', $assosiacao) || is_array($assosiacao)) {

            foreach ($assosiacao as $ass) {
                $nameclass = ucfirst($ass);

                $novo = str_replace('$name', $ass, $assfile);
                $novo = str_replace('$class', $nameclass, $novo);
                $novo = str_replace('$pai', $name, $novo);

                $linegetandset .= "$novo \n\t";
            }
        } else {
            $nameclass = ucfirst($assosiacao);
            $novo = str_replace('$name', $assosiacao, $assfile);
            $novo = str_replace('$class', $nameclass, $novo);
            $novo = str_replace('$pai', $name, $novo);

            $linegetandset .= "$novo \n\t";
        }

    }
}


        // composicao
        if(!empty($composition)) {
            if ($this->strCharFind(',', $composition)) {

                $composition = is_array($composition) ? $composition : explode(',', $composition);

                foreach ($composition as $ass) {
                    $asslines .= "private $ass; \n\t";

                }

            } else {
                $asslines .= "private $composition; \n\t";

            }

            if (!empty($composition)) {

                $comfile = file_get_contents($patch . '/templates/models/composition.php');
                $methodsFile = file_get_contents($patch . '/templates/models/methods.php');

                $composition = is_array($composition) ? $composition : explode(',', $composition);
                $load = '';
                $save = '';
                $delete = '';

                // insere os get and Set
                if ($this->strCharFind(',', $composition) || is_array($composition)) {

                    foreach ($composition as $com) {
                        $nameclass = ucfirst($com);

                        $novo = str_replace('$name', $com, $comfile);
                        $novo = str_replace('$class', $nameclass, $novo);
                        $novo = str_replace('$pai', $name, $novo);

                        $comline .= "$novo \n\t";

                        $load .= $this->loadcomposition($com,$patch,$name);
                        $save .= $this->savecomposition($com,$patch,$name);
                        $delete .= $this->deletecomposition($com,$patch,$name);

                    }
                } else {
                    $nameclass = ucfirst($composition);
                    $novo = str_replace('$name', $assosiacao, $assfile);
                    $novo = str_replace('$class', $nameclass, $novo);
                    $novo = str_replace('$pai', $name, $novo);

                    $comline .= "$novo \n\t";


                    $load = $this->loadcomposition($composition,$patch,$name);
                    $save = $this->savecomposition($composition,$patch,$name);
                    $delete = $this->deletecomposition($composition,$patch,$name);


                }

                // implementar metodos

                $methodsFile = str_replace('$loadComposite',$load,$methodsFile);
                $methodsFile = str_replace('$saveComposite',$save,$methodsFile);
                $methodsFile = str_replace('$deleteComposite',$delete,$methodsFile);
            }

        }else{
            $methodsFile = empty($methodsFile)?file_get_contents($patch . '/templates/models/methods.php'):$methodsFile;



            $methodsFile = str_replace('$loadComposite','',$methodsFile);
            $methodsFile = str_replace('$saveComposite','',$methodsFile);
            $methodsFile = str_replace('$deleteComposite','',$methodsFile);
        }

        // agreggation

        // composicao
        if(!empty($aggregate)) {
            if ($this->strCharFind(',', $aggregate)) {

                $aggregate = is_array($aggregate) ? $aggregate : explode(',', $aggregate);

                foreach ($aggregate as $ass) {
                    $asslines .= "private $ass; \n\t";

                }

            } else {
                $asslines .= "private $aggregate; \n\t";

            }

            if (!empty($aggregate)) {

                $comfile = file_get_contents($patch . '/templates/models/composition.php');

                $methodsFile = empty($methodsFile)?file_get_contents($patch . '/templates/models/methods.php'):$methodsFile;

                $aggregate = is_array($aggregate) ? $aggregate : explode(',', $aggregate);
                $load = '';
                $save = '';
                $delete = '';

                // insere os get and Set
                if ($this->strCharFind(',', $aggregate) || is_array($aggregate)) {

                    foreach ($aggregate as $com) {
                        $nameclass = ucfirst($com);

                        $novo = str_replace('$name', $com, $comfile);
                        $novo = str_replace('$class', $nameclass, $novo);
                        $novo = str_replace('$pai', $name, $novo);

                        $comline .= "$novo \n\t";

                        $load .= $this->loadAggregate($name,$patch,$com,$pivot);
                        $save .= $this->saveAggregate($name,$patch,$com,$pivot);
                        $delete .= $this->deletecomposition($pivot,$patch,$name);

                    }
                } else {
                    $nameclass = ucfirst($composition);
                    $novo = str_replace('$name', $assosiacao, $assfile);
                    $novo = str_replace('$class', $nameclass, $novo);
                    $novo = str_replace('$pai', $name, $novo);

                    $comline .= "$novo \n\t";


                    $load = $this->loadAggregate($name,$patch,$aggregate,$pivot);
                    $save = $this->savecomposition($name,$patch,$aggregate,$pivot);
                    $delete = $this->deleteaggregate($pivot,$patch,$name);


                }

                // implementar metodos

                $methodsFile = str_replace('$loadAggregate',$load,$methodsFile);
                $methodsFile = str_replace('$saveAggregate',$save,$methodsFile);
                $methodsFile = str_replace('$deleteAggregate',$delete,$methodsFile);
            }

            /// limpa
        }else{
            $methodsFile = str_replace('$loadAggregate','',$methodsFile);
            $methodsFile = str_replace('$saveAggregate','',$methodsFile);
            $methodsFile = str_replace('$deleteAggregate','',$methodsFile);
        }


        if(empty($aggregate) && empty($composition))
            $methodsFile = '';

        $file = str_replace('$assosiacao',$asslines,$file);
        $file = str_replace('$fields',$lines,$file);
        $file = str_replace('$getset',$linegetandset,$file);
        $file = str_replace('$composition',$comline,$file);
        $file = str_replace('$methods',$methodsFile,$file);


        file_put_contents($out,$file);

        }


        private function addAttribute($fields,$prefix = ''){
            $lines = '';
            $fields = is_array($fields)?$fields:explode(',', $fields);


            if($this->strCharFind(',', $fields) || is_array($fields)) {
                // insere os compos

                foreach ($fields as $field) {
                    if(!empty($prefix))
                          $field = $field.'_'.$prefix;

                    $lines .= "parent::addAttribute('$field'); \n\t";


                }

            }else{
                if(!empty($prefix))
                    $fields = $fields.'_'.$prefix;

                $lines .= "parent::addAttribute('$fields'); \n\t";
            }
            return $lines;
        }

        private function loadcomposition($name,$patch,$pai){

            $load = file_get_contents($patch.'/templates/models/metods/loadComposite.php');

            $nameclass = ucfirst($name);

            $novo = str_replace('$name', $name, $load);
            $novo = str_replace('$class', $nameclass, $novo);
            $novo = str_replace('$pai', strtolower($pai), $novo);


           return "$novo \n\t";

        }

    private function savecomposition($name,$patch,$pai){


        $save = file_get_contents($patch.'/templates/models/metods/saveComposite.php');

        $nameclass = ucfirst($name);

        $novo = str_replace('$name', $name, $save);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);


        return "$novo \n\t";

    }

    private function deletecomposition($name,$patch,$pai){


        $delete = file_get_contents($patch.'/templates/models/metods/deleteComposite.php');

        $nameclass = ucfirst($name);

        $novo = str_replace('$name', $name, $delete);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);


        return   "$novo \n\t";

    }


    private function loadAggregate($name,$patch,$pai,$pivot){

        $load = file_get_contents($patch.'/templates/models/metods/loadAggregate.php');

        $nameclass = ucfirst($pai);

        $novo = str_replace('$name', strtolower($name), $load);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);
        $novo = str_replace('$pivot', $pivot, $novo);


        return "$novo \n\t";

    }

    private function saveAggregate($name,$patch,$pai,$pivot){


        $save = file_get_contents($patch.'/templates/models/metods/saveAggregate.php');

        $nameclass = ucfirst($name);

        $novo = str_replace('$name', strtolower($name), $save);
        $novo = str_replace('$class', $nameclass, $novo);
        $novo = str_replace('$pai', strtolower($pai), $novo);
        $novo = str_replace('$pivot', $pivot, $novo);


        return "$novo \n\t";

    }


    private function deleteaggregate($pivot,$patch,$pai){


        $delete = file_get_contents($patch.'/templates/models/metods/deleteComposite.php');


        $novo = str_replace('$class', $pivot, $delete);
        $novo = str_replace('$pai', $pai, $novo);


        return   "$novo \n\t";

    }

private function strCharFind($needle,$haystack){
        $return = FALSE;

        if(!is_array($haystack)) {
            $arr = str_split($haystack, 1);
            foreach ($arr as $value) {
                if ($value == strtolower($needle) || $value == strtoupper($needle)) {
                    $return = TRUE;
                }
            }
        }
        return $return;
    }
}