<?php

namespace Mcri\GestBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Mcri\GestBundle\Entity\Stock;
use Mcri\GestBundle\Entity\PcStock;
use Mcri\GestBundle\Entity\PeriphStock;
use Mcri\GestBundle\Form\PcStockType;
use Mcri\GestBundle\Form\PeriphStockType;
use Mcri\GestBundle\Form\DirectionType;
use Mcri\GestBundle\Form\ServiceType;
use Mcri\GestBundle\Form\PersonnelType;
use Mcri\GestBundle\Form\PeriphUsedType;
use Mcri\GestBundle\Form\PcUsedType;
use Mcri\GestBundle\Form\MarqueType;
use Mcri\GestBundle\Form\TypePeriphCompType;
use Mcri\GestBundle\Entity\ComposantStock;
use Mcri\GestBundle\Form\ComposantStockType;
use Mcri\GestBundle\Entity\Marque;
use Mcri\GestBundle\Entity\TypePeriphComp;
use Mcri\GestBundle\Entity\Used;
use Mcri\GestBundle\Entity\PcUsed;
use Mcri\GestBundle\Entity\PeriphUsed;
use Mcri\GestBundle\Entity\ComposantUsed;
use Mcri\GestBundle\Entity\Personnel;
use Mcri\GestBundle\Entity\Direction;
use Mcri\GestBundle\Entity\Service;
use Doctrine\ORM\EntityRepository;

//$session = new Session(new PhpBridgeSessionStorage());
//$session->start();
class GestController extends Controller
{
    private function afficher()
    {
        global $nbPC,$affichtab,$affichtabComp;

        /** Debut mitady PC complet **/

            $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');

            $pc=$rep->mitadyCateg('pc');
            $pcuse = 0;
            foreach ($pc as $pcfound)
            {
                $nbPC = $nbPC +($pcfound->getNombre());
                $npc=$pcfound->getUseds();
                $npcarray=$npc->toArray();
                $pcuse = $pcuse + count($npcarray);
            }

        /** Fin mitady PC complet **/

        /** Debut mitady Perpiph**/
            $nbperiph=0; $nbused=0; $affichtab=array();

            $r = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:TypePeriphComp');

            $typePeriph= $r->ListePeriphCompByCat('peripherique');

            foreach($typePeriph as $typParcour)
            {
                $found=$rep->MitadyAmTypeBis($typParcour['libelletyp'],'PeriphStock');

                if(!empty($found))
                {
                   foreach($found as $foundParcour)
                    {
                        $nbperiph = $nbperiph + ($foundParcour->getNombre());
                        $n = $foundParcour->getUseds();
                        $narray=$n->toArray();
                        $nbused = $nbused + count($narray);
                    }

                    $affichtab = $affichtab + array($typParcour['libelletyp']=>array('Nombre'=>$nbperiph,
                                                                       'Type'=>$foundParcour->getType(),
                                                                       'Used'=>$nbused,
                    ));

                    $nbperiph=0; $nbused=0;  $found=null;
                }
            }

        /** Fin mitady Periph **/

        /** Debut mitady Composant**/
            $nbperiph=0; $nbperiphused=0; $affichtabComp=array();
            $typeComp= $r->ListePeriphCompByCat('composant');
            foreach($typeComp as $typCompParcour)
            {
                $foundComp=$rep->MitadyAmTypeBis($typCompParcour['libelletyp'],'ComposantStock');

                if(!empty($foundComp))
                {
                   foreach($foundComp as $foundCompParcour)
                    {
                        $nbperiph = $nbperiph + ($foundCompParcour->getNombre());
                        $ncomp = $foundCompParcour->getUseds();
                        $ncomparray=$ncomp->toArray();
                        $nbperiphused = $nbperiphused + count($ncomparray);
                    }

                    $affichtabComp = $affichtabComp + array($typCompParcour['libelletyp']=>array('Nombre'=>$nbperiph,
                                                                       'Type'=>$foundCompParcour->getType(),
                                                                       'Used'=>$nbperiphused,
                    ));

                    $nbperiph=0; $nbperiphused=0; $foundComp=null;
                }
            }
        /** Fin mitady Composant **/

        return $this->render('McriGestBundle:Gest:index.html.twig',array('nombrePC'=>$nbPC,
                                                                         'usePC'=>$pcuse,
                                                                         'affich'=>$affichtab,
                                                                         'affichcomp'=>$affichtabComp,
                            ));
    }


    public function indexAction()
    {
        global $nbPC,$affichtab,$affichtabComp;
        return $this->afficher();

    }


    public function emmagpcAction()
    {

    	$pc=new PcStock();
    	$form = $this->createForm(new PcStockType, $pc);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST')
        {
                $form->bind($request);
                $em=$this->getDoctrine()->getManager();
                $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');

                $pcsearch=$rep->mitadyCateg('pc');
                $mrqpost=$form['Marque']->getData();
                $nbpost=$form['Nombre']->getData();
                $hddpost=$form['HDD']->getData();
                $rampost=$form['RAM']->getData();
                $cpupost=$form['CPU']->getData();
                $gpupost=$form['Gpu']->getData();
                $gpucappost=$form['GpuCapacity']->getData();
                $cmpost=$form['CarteMere']->getData();

                foreach ($pcsearch as $pcfound)
                {
                    if($mrqpost==$pcfound->getMarque() AND $hddpost==$pcfound->getHDD() AND $rampost==$pcfound->getRAM() AND $cpupost==$pcfound->getCPU()
                        AND $gpupost==$pcfound->getGpu()  AND $gpucappost==$pcfound->getGpuCapacity()  AND $cmpost==$pcfound->getCarteMere()
                        )
                    {
                        $newnb = $pcfound->getNombre() + $nbpost;
                        $pcfound->setNombre($newnb);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('info', 'PC complet bien enregistré(s) !');
                        return $this->redirect($this->generateUrl('mcri_accueil'));
                    }
                }

                if ($form->isValid()) //equivalent elseif
                {
                    $em->persist($pc);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('info', 'PC complet bien enregistré(s) !');
                    return $this->redirect($this->generateUrl('mcri_accueil'));
                }
        }
    	return $this->render('McriGestBundle:Gest:emmagasinerpc.html.twig', array('form'=> $form->createView(),));
    }



    public function periphOuComposant($varEntity)
    {
            global $form,$request, $periph;
            $form->bind($request); //tsy adino mts ty refa aka ny variable POST

            $typpost=$form['type']->getData();
            $mrqpost=$form['Marque']->getData();
            $nbpost=$form->get('Nombre')->getData();
            $rep = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('McriGestBundle:Stock');
            $prph=$rep->MitadyAmTypeBis($typpost,$varEntity);
            $em=$this->getDoctrine()->getManager();
            if(empty($prph))
            {
                if ($form->isValid())
                {
                    $em->persist($periph);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('info', 'Matériel(s) bien enregistré(s) !');
                    return $this->redirect($this->generateUrl('mcri_accueil'));
                }
            }
            elseif(!empty($prph))
            {
                foreach ($prph as $prphfound)
                {
                    if($varEntity=='PeriphStock')
                    {

                        if ($mrqpost==$prphfound->getMarque())
                        {
                            $newnb = $prphfound->getNombre() + $nbpost;
                            $prphfound->setNombre($newnb);
                            $em->flush();
                            $this->get('session')->getFlashBag()->add('info', 'Matériel(s) bien enregistré(s) !');
                            return $this->redirect($this->generateUrl('mcri_accueil'));
                        }
                    }
                    elseif ($varEntity=='ComposantStock')
                    {
                        $cappost=$form['Capacite']->getData();
                        if ($mrqpost==$prphfound->getMarque() AND $cappost==$prphfound->getCapacite())
                        {
                            $newnb = $prphfound->getNombre() + $nbpost;
                            $prphfound->setNombre($newnb);
                            $em->flush();
                            $this->get('session')->getFlashBag()->add('info', 'Matériel(s) bien enregistré(s) !');
                            return $this->redirect($this->generateUrl('mcri_accueil'));
                        }
                    }
                }
                if ($form->isValid()) // mitovy amn'ny elseif @ if eo ambony
                {
                    $em->persist($periph);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('info', 'Matériel(s) bien enregistré(s) !');
                    return $this->redirect($this->generateUrl('mcri_accueil'));
                }
            }
    }

    public function emmagperiphAction()
    {
        global $form,$request, $periph;

        $periph=new PeriphStock();
        $form = $this->createForm(new PeriphStockType, $periph);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST')
        {
            return $this->periphOuComposant('PeriphStock') ;
        }
        return $this->render('McriGestBundle:Gest:emmagasinerperiph.html.twig', array('form'=> $form->createView(),));
    }


    public function emmagcompAction()
    {
        global $form,$request, $periph;

        $periph=new ComposantStock();
        $form = $this->createForm(new ComposantStockType, $periph);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST')
        {
            return $this->periphOuComposant('ComposantStock') ;
        }
        return $this->render('McriGestBundle:Gest:emmagasinercomp.html.twig',array('form'=> $form->createView(),));
    }



    public function allocpcAction()
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');

        $pc=$rep->mitadyCateg('pc');
        foreach ($pc as $pp) //micalcule sy mreattribuer ny nombre dispo
        {
            $res = $pp->getUseds();
            $resarray=$res->toArray();
            $nb = $pp->getNombre();
            $pp->setNombre($nb - count($resarray));
        }

        return $this->render('McriGestBundle:Gest:allocpc.html.twig',array('pc'=>$pc,));
    }

    public function allocpcfinalAction($id)
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');
        $pc=$rep->find($id);

        $utiliz = new PcUsed();
        $form = $this->createForm(new PcUsedType, $utiliz);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            $persrep=$this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Personnel');
            $postpers = $persrep->mitadyPers($form['personnel']->getData().'');

            if( $form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $pc->addUsed($utiliz);
                $utiliz->setEtat('fonctionnel');
                $utiliz->setDateAlloc(new \DateTime());
                $postpers->addUsed($utiliz);
                $em->persist($utiliz);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'PC affécté avec succès !');
                return $this->redirect($this->generateUrl('mcri_allocpc'));
            }
        }
        return $this->render('McriGestBundle:Gest:allocpcfinal.html.twig',array('pc'=>$pc,'form'=> $form->createView(),));
    }


    public function allocperiphAction()
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');
        $periph=$rep->mitadyCateg('peripherique');
        foreach ($periph as $pp) //micalcule sy mreattribuer ny nombre dispo
        {
            $res = $pp->getUseds();
            $resarray=$res->toArray();
            $nb = $pp->getNombre();
            $pp->setNombre($nb - count($resarray));
        }

        return $this->render('McriGestBundle:Gest:allocperiph.html.twig',array('periph'=>$periph,));
    }



    public function allocperiphfinalAction($id)
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');
        $periph=$rep->find($id);
        $t = $periph->getType();

        $utiliz = new PeriphUsed();
        $form = $this->createForm(new PeriphUsedType, $utiliz);
        $request = $this->get('request');
        $form->bind($request);

        if ($request->getMethod() == 'POST')
        {

            $persrep=$this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Personnel');
            $postpers = $persrep->mitadyPers($form['personnel']->getData().'');

            if( $form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $periph->addUsed($utiliz);
                $postpers->addUsed($utiliz);
                $em->persist($utiliz);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', $t.' affécté(e) avec succès !');
                return $this->redirect($this->generateUrl('mcri_allocperiph'));
            }
        }
        return $this->render('McriGestBundle:Gest:allocperiphfinal.html.twig',array('form'=> $form->createView(),'type'=>$t,));
    }


    public function ajoutdirAction()
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Direction');

        $affdir=$rep->findAll();

        $dir= new Direction();
        $form=$this->createForm(new DirectionType, $dir);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            if( $form->isValid() )
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($dir);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Direction bien enregistrée !');
                return $this->redirect($this->generateUrl('mcri_ajoutdir'));
            }

        }

        return $this->render('McriGestBundle:Gest:ajoutdir.html.twig',array('form'=> $form->createView(),'dir'=>$affdir));
    }

    public function ajoutservAction()
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Direction');

        $dir=$rep->findAll();

        $serv=new Service();
        $form=$this->createForm(new ServiceType, $serv);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            $dirpost=$form['dir']->getData();
            if( $form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $dirpost->addService($serv);
                $em->persist($serv);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Service bien enregistré !');
                return $this->redirect($this->generateUrl('mcri_ajoutserv'));
            }

        }

        return $this->render('McriGestBundle:Gest:ajoutserv.html.twig',array('form'=> $form->createView(),'dir'=>$dir,));
    }


    public function ajoutpersAction()
    {

        $form=$this->createFormBuilder()
                    ->add('nomDir','entity',array(
                                                   'empty_value' => 'Veuillez choisir une direction sur laquelle sera affectée cette personne',
                                                   'class' =>'McriGestBundle:Direction',
                                                   'query_builder' => function (EntityRepository $er) {
                                                              return $er->createQueryBuilder('d')
                                                                        ->orderBy('d.nomDir', 'ASC');
                                                    },

                                                   'property' => 'nomDir',

                        ))
                    ->add('Continuer', 'submit', array('label' => 'Continuer','attr' => array('class' => 'btn btn-primary col-sm-offset-3 col-sm-6'),))
                    ->getForm();


        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->get('Continuer')->isClicked())
        {

            $dirpost=$form['nomDir']->getData();
            $this->getRequest()->getSession()->set('dirpost',$dirpost);

            return $this->redirect($this->generateUrl('mcri_ajoutpersfinal'));
        }

        return $this->render('McriGestBundle:Gest:ajoutpers.html.twig',array('form'=> $form->createView(),));
    }


    public function ajoutpersfinalAction()
    {
        $dirpost=$this->getRequest()->getSession()->get('dirpost');
        $id=$dirpost->getId();
        $pers=new Personnel();
        $form2=$this->createForm(new PersonnelType($id), $pers);

        $request = $this->get('request');

        if ($request->getMethod() == 'POST')
        {

            $form2->bind($request);
            $servpost=$form2['serv']->getData();

            if( $form2->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $servpost->addPerso($pers);
                $em->persist($pers);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Personnel bien enregistré !');
                return $this->redirect($this->generateUrl('mcri_ajoutpers'));
            }
        }
        return $this->render('McriGestBundle:Gest:ajoutpersfinal.html.twig',array('form2'=>$form2->createView(),'selection'=>$dirpost->getNomDir(),));
    }


    public function voirPeriph($mat)
    {
        $materiel=array(); $cpt=0;
            foreach ($mat as $matp)
            {
                $s=$matp->getStock();
                $p=$matp->getPersonnel();
                $typ= $s->getType();
                $det=$p->getNomPers().' '.$p->getPrenomPers();
                $mark= $s->getMarque();
                $materiel=$materiel+array($cpt=>array('ns'=>$matp->getNumeroSerie(),
                                                      't'=>$typ,
                                                      'etat'=>$matp->getEtat(),
                                                      'marque'=>$mark,
                                                      'detenteur'=>$det,
                                                      'dateAlloc'=>$matp->getDateAlloc(),
                                                      'datenewdet'=>$matp->getDateNewDetenteur(),
                                                      'id'=>$matp->getId(),
                ));
                $cpt++;
            }
        
        return $materiel;
    }


    public function voirComp($comp)
    {
        $compos=array(); $cpt=0;
        foreach ($comp as $matp)
            {
                $s=$matp->getStock();
                $p=$matp->getPersonnel();
                $typ= $s->getType();
                $description = $s->getDescription();
                $capacite = $s->getCapacite();
                $det=$p->getNomPers().' '.$p->getPrenomPers();
                $mark= $s->getMarque();
                $compos=$compos+array($cpt=>array(    'ns'=>$matp->getNumeroSerie(),
                                                      't'=>$typ,
                                                      'marque'=>$mark,
                                                      'detenteur'=>$det,
                                                      'dateAlloc'=>$matp->getDateAlloc(),
                                                      'capacite'=>$capacite,
                                                      'description'=>$description,
                                                      'pcprop'=>$matp->getPc()->getNumeroSerie(),
                ));
                $cpt++;
            }
        return $compos;

    }

    public function voirPC($pc)
    {
        $ordi=array(); $cpt2=0;
            foreach ($pc as $pcp)
            {
                $s=$pcp->getStock();
                $p=$pcp->getPersonnel();
                $det=$p->getNomPers().' '.$p->getPrenomPers();
                $mark=$s->getMarque();
                $hdd=$s->getHDD();
                $ram=$s->getRAM();
                $cpu=$s->getCPU();
                $graph=$s->getGpu();
                $graphcap=$s->getGpuCapacity();
                $cm=$s->getCarteMere();
                $ordi=$ordi+array($cpt2=>array(       'ns'=>$pcp->getNumeroSerie(),
                                                      'etat'=>$pcp->getEtat(),
                                                      'marque'=>$mark,
                                                      'hdd'=>$hdd,
                                                      'ram'=>$ram,
                                                      'cpu'=>$cpu,
                                                      'graph'=>$graph,
                                                      'graphcap'=>$graphcap,
                                                      'cm'=>$cm,
                                                      'detenteur'=>$det,
                                                      'datenewdet'=>$pcp->getDateNewDetenteur(),
                                                      'causenewdet'=>$pcp->getCauseNewDet(),
                                                      'dateAlloc'=>$pcp->getDateAlloc(),
                                                      'id'=>$pcp->getId(),
                ));
                $cpt2++;
            }
        return $ordi;
    }


    public function voirAction()
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Used');

        $form= $this->createFormBuilder()
                    ->add('personnel','entity',array(
                                                   'empty_value' => 'Liste du Personnel',
                                                   'class' =>'McriGestBundle:Personnel',
                                                   'query_builder' => function (EntityRepository $er) {
                                                              return $er->createQueryBuilder('p')
                                                                        ->orderBy('p.nomPers', 'ASC');
                                                    },
                        ))
                    ->getForm()
        ;
        $request = $this->get('request');
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            $persrep=$this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Personnel');
            $perspost = $persrep->mitadyPers($form['personnel']->getData().'');
            $mat=$rep->mitadyCategAdvanced('peripherique',$perspost->getId());
            $pc=$rep->mitadyCategAdvanced('pc',$perspost->getId());
            $comp=$rep->mitadyCategAdvanced('composant',$perspost->getId());

            $materiel = $this->voirPeriph($mat);
            $ordi = $this->voirPC($pc);
            $compos = $this->voirComp($comp);

            return $this->render('McriGestBundle:Gest:voir.html.twig',array('form'=>$form->createView(),'mat'=>$materiel,'ordi'=>$ordi,'compos'=>$compos,));
        }


        $mat=$rep->mitadyCateg('peripherique');
        $materiel = $this->voirPeriph($mat);

        $pc=$rep->mitadyCateg('pc');
        $ordi = $this->voirPC($pc);

        $comp=$rep->mitadyCateg('composant');
        $compos = $this->voirComp($comp);

        return $this->render('McriGestBundle:Gest:voir.html.twig',array('form'=>$form->createView(),'mat'=>$materiel,'ordi'=>$ordi,'compos'=>$compos,));
    }


    public function actionpcAction($id)
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Used');
        $utiliz=$rep->find($id);
        $lastState=$utiliz->getEtat().''; //dernier état 
        $pc=$utiliz->getStock();

        $form= $this->createFormBuilder($utiliz)
                    ->add('personnel','entity',array(
                                                   'class' =>'McriGestBundle:Personnel',
                                                   'query_builder' => function (EntityRepository $er) {
                                                              return $er->createQueryBuilder('p')
                                                                        ->orderBy('p.nomPers', 'ASC');
                                                    },

                        ))
                    ->add('causeNewDet','textarea', array('required'=>false,))
                    ->add('etat','text')
                    ->add('Edit', 'submit', array('label' => 'Modifier','attr' => array('class' => 'btn btn-primary col-sm-offset-3 col-sm-3'),))
                    ->add('Delete', 'submit', array('label' => 'Supprimer','attr' => array('class' => 'btn btn-danger col-sm-offset-2 col-sm-5'),))
                    ->add('print', 'submit', array('label' => 'Exporter en PDF','attr' => array('class' => 'btn btn-primary  col-sm-offset-2 col-sm-5'),))
                    ->add('etatbtn', 'submit', array('label' => 'Enregistrer déclaration','attr' => array('class' => 'btn btn-warning  col-sm-offset-2 col-sm-5'),))
                    ->getForm();
        
        /**$form2= $this->createFormBuilder()
                     ->add('etat','text')
                     ->add('etatbtn', 'submit', array('label' => 'Enregistrer déclaration','attr' => array('class' => 'btn btn-warning  col-sm-offset-2 col-sm-5'),))
                     ->getForm();**/

        $request = $this->get('request');
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getEntityManager();
         
        if ($form->get('Edit')->isClicked())
        {
            $persrep=$this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Personnel');
            $perspost = $persrep->mitadyPers($form['personnel']->getData().'');
            if( $form->isValid())
            {
                $perspost->addUsed($utiliz);

                if ($utiliz->getComposants()!=null) // changement en parrallele de la detention des composants si ce PC a deja changé de composant auparavant
                {
                    $c=$utiliz->getComposants();
                    foreach ($c as $cp) 
                    {
                        $cp->setPersonnel($perspost);
                    }
                }

                $utiliz->setDateNewDetenteur(new \DateTime());
                $em->persist($utiliz);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Changement de détention du PC effectué!');
                return $this->redirect($this->generateUrl('mcri_voir'));
            }
        }
        elseif ($form->get('print')->isClicked()) 
        {
            $today = new \DateTime();
            $html = $this->renderView('McriGestBundle:Gest:printpc.html.twig',array('pc'=>$pc,'utiliz'=>$utiliz,'today'=>$today,));
            $html2pdf = $this->get('html2pdf_factory')->create();
            $html2pdf->pdf->SetDisplayMode('real');
            $html2pdf->writeHTML($html);
            return new Response($html2pdf->Output($utiliz->getNumeroSerie().'.pdf'), 200, array('Content-Type' => 'application/pdf'));
        }

        
        if ($form->get('etatbtn')->isClicked()) 
        {

           if ($lastState=='fonctionnel') 
            {
                $nvetat=$form['etat']->getData().'';
                $utiliz->setEtat('DEFECTUEUX ('.$nvetat.')');
                $em->persist($utiliz);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Modification sur Etat du PC effectué!');
                return $this->redirect($this->generateUrl('mcri_voir'));
            }
           else 
            {
                $utiliz->setEtat('fonctionnel');
                $em->persist($utiliz);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Modification sur Etat du PC effectué!');
                return $this->redirect($this->generateUrl('mcri_voir'));
            }
        } 

        $repcomp = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');
        $comp=$repcomp->mitadyCateg('composant');
        foreach ($comp as $pp)
        {
            $res = $pp->getUseds();
            $resarray=$res->toArray();
            $nb = $pp->getNombre();
            $pp->setNombre($nb - count($resarray));
        }

         return $this->render('McriGestBundle:Gest:actionpc.html.twig',array('form'=>$form->createView(),'pc'=>$pc,'utiliz'=>$utiliz,'comp'=>$comp,));
    }



    public function actionperiphAction($id)
    {
        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Used');
        $utiliz=$rep->find($id);
        $form= $this->createFormBuilder($utiliz)
                    ->add('personnel','entity',array(
                                                   'class' =>'McriGestBundle:Personnel',
                                                   'query_builder' => function (EntityRepository $er) {
                                                              return $er->createQueryBuilder('p')
                                                                        ->orderBy('p.nomPers', 'ASC');
                                                    },

                        ))
                    ->add('Edit', 'submit', array('label' => 'Modifier','attr' => array('class' => 'btn btn-primary col-sm-offset-1 col-sm-4'),))
                    ->add('Delete', 'submit', array('label' => 'Supprimer','attr' => array('class' => 'btn btn-danger col-sm-offset-2 col-sm-4'),))
                    ->getForm();
        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->get('Edit')->isClicked())
        {
            $persrep=$this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Personnel');
            $perspost = $persrep->mitadyPers($form['personnel']->getData().'');
            if( $form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $perspost->addUsed($utiliz);
                $utiliz->setDateNewDetenteur(new \DateTime());
                $em->persist($utiliz);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'Changement de détention du péripherique effectué!');
                return $this->redirect($this->generateUrl('mcri_voir'));
            }
            elseif ($form->get('cancel')->isClicked())
            {
                return $this->redirect($this->generateUrl('mcri_voir'));
            }
        }
        return $this->render('McriGestBundle:Gest:actionperiph.html.twig', array('utiliz'=>$utiliz, 'form'=>$form->createView(),));
    }


    public function changcompAction($id,$idcomp)
    {
        $compused=new ComposantUsed();
        $form= $this->createFormBuilder($compused)
                    ->add('NumeroSerie','text', array('required'=>false,))
                    ->add('ok', 'submit', array('label' => 'Confirmer','attr' => array('class' => 'btn btn-primary col-sm-offset-1 col-sm-4'),))
                    ->add('cancel', 'submit', array('label' => 'Annuler','attr' => array('class' => 'btn btn-danger col-sm-offset-2 col-sm-4'),))
                    ->getForm();

        $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Used');
        $utiliz=$rep->find($id); //pcused

        $repcomp = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Stock');
        $compstock=$repcomp->find($idcomp);

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->get('ok')->isClicked())
        {
            $compstock->addUsed($compused);
            $utiliz->getPersonnel()->addUsed($compused);
            $em = $this->getDoctrine()->getEntityManager();
            $utiliz->addComposant($compused);
            $em->persist($compused);
            $em->flush();
            $this->get('session')->getFlashBag()->add('info', 'Changement de Composant bien effectué');
            return $this->redirect($this->generateUrl('mcri_voir'));
        }
        elseif ($form->get('cancel')->isClicked())
        {
            return $this->redirect($this->generateUrl('mcri_voir'));
        }

        return $this->render('McriGestBundle:Gest:changcomp.html.twig', array('form'=>$form->createView(),'utiliz'=>$utiliz,'comp'=>$compstock,));
    }

    public function printpcAction()
    {
        return $this->render('McriGestBundle:Gest:printpc.html.twig',array('pc'=>$pc,'utiliz'=>$utiliz,));
    }

    public function ajoutmarqueAction()
    {
        $marque= new Marque();
        $form=$this->createForm(new MarqueType, $marque);
        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->get('save')->isClicked())
        {
            $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:Marque');

            $lib = strtoupper($form['libelle']->getData().''); 
            $typ = $form['type']->getData().'';

            $res = $rep->mitadyDoublon($lib,$typ);

            if ($res!=null) 
            {
                $this->get('session')->getFlashBag()->add('erreur', 'ERREUR, cette marque existe déja pour la catégorie de matériel séléctionnée');
                return $this->redirect($this->generateUrl('mcri_ajoutmarque'));
            }
            elseif ($res==null) 
            {
                if( $form->isValid())
                {
                    $this->get('session')->getFlashBag()->add('info', 'Marque de matériel bien enregistré!');
                    $em = $this->getDoctrine()->getEntityManager(); 
                    $em->persist($marque);
                    $em->flush();
                    return $this->redirect($this->generateUrl('mcri_ajoutmarque'));
                }
            }
        }

        return $this->render('McriGestBundle:Gest:ajoutmarque.html.twig', array('form'=>$form->createView(),));
    }

    public function ajouttypeAction()
    {
        $type = new TypePeriphComp(); 
        $form=$this->createForm(new TypePeriphCompType, $type);

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->get('save')->isClicked())
        {
            $rep = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('McriGestBundle:TypePeriphComp');

            $lib = strtoupper($form['libelletyp']->getData().''); 
            $categ = $form['categ']->getData().'';

            $res = $rep->mitadyDoublon($lib,$categ);

            if ($res!=null) 
            {
                $this->get('session')->getFlashBag()->add('erreur', 'ERREUR, ce type existe déja pour la catégorie de matériel séléctionnée');
                return $this->redirect($this->generateUrl('mcri_ajouttype'));
            }
            elseif ($res==null) 
            {
                if( $form->isValid())
                {
                    $this->get('session')->getFlashBag()->add('info', 'Type de matériel bien enregistré!');
                    $em = $this->getDoctrine()->getEntityManager(); 
                    $em->persist($type);
                    $em->flush();
                    return $this->redirect($this->generateUrl('mcri_ajouttype'));
                }
            }
        }

        return $this->render('McriGestBundle:Gest:ajouttype.html.twig', array('form'=>$form->createView(),));
    }
}
