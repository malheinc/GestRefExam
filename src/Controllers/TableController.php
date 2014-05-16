<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Models\Generics\ClassFactory;
use Helpers\PhpHelpers;
use Symfony\Component\Form\FormError;
use Models\Entities\TableEntity;
use Models\Managers\TableManager;
use Forms\AjoutTableForm;


class TableController implements ControllerProviderInterface
{
    //La fonction connect contient toutes les routes au niveau controleur de 
    //l'application.
    //Les paramètres passés dans l'URL sont noté entre crochets {{ paramètre }}.
    public function connect(Application $app)
    {
      $controllers = $app['controllers_factory'];

      $controllers->match('/', __CLASS__ . '::indexAction')
                ->bind('homepage');
            
      $controllers->get('/contenu-table/{tableId}', __CLASS__ . '::fullTableAction')
                ->bind('liste')
                ->assert('tableId', '\w*');

      $controllers->delete('/suppression-enregistrement/{tableId}/', __CLASS__ . '::deleteAction')
                ->bind('delete')
                ->assert('tableId', '\w*');
      
      $controllers->match('/ajout-enregistrement/{tableId}/', __CLASS__ . '::createAction')
                ->bind('create')
                ->assert('tableId', '\w*')
                ->method('GET|POST');

      
      $controllers->match('/modification-enregistrement/{tableId}/{valueUpdate}/', __CLASS__ . '::updateAction')
                ->bind ('update')
                ->assert('tableId', '\w*')
                ->method('GET|PUT');
      
        return $controllers;
    }
    
    
/******************************************************************************/    
    
    
//La fonction indexAction va permettre l'affichage de la table de paramètrage.  
    
public function indexAction(Request $request, Application $app)
    {
    //Initialisation de  'tableManager permettant de faire appel aux fonctions
    //de TableManager.php
    
    $tableManager = ClassFactory::getManager('TableManager');
    
    /*On retourne le résultat de la fonction 'getTableParam de 'TableManager.php
    dans le template 'TableParametrage.html.twig*/
    
            return $app ['twig']->render('TableParametrage.html.twig', 
                array ('params'=> $tableManager->getTableParam($app)));       
    }
    
    
/******************************************************************************/
    
    
    /*'fullTableAction' permet l'affichage de la table en fonction de la ligne 
    choisie dans la table de paramétrage*/
  
    public function fullTableAction(Request $request, Application $app)
    {
        /*
         * On initialise TableManager.
         */
        
        $tableManager = ClassFactory::getManager('TableManager');
        
        /*
         * On va chercher le nom des champs clés de la table.
         */
        
        $refChampsCle = $tableManager->findOne(
            array(
                'REF_NOMTABLE' => $request->get('tableId'),
            ),
            'REF_CHAMPS_CLE'
        );
        
        $header = array();
        $data = array();
        $cles = array();
        
        /*
         * Au moment d'afficher, on regarde si la table existe, si non, on affiche
         * un message d'erreur.
         */       
        
        try {
            $data = $tableManager->affichageDynamique(
                $app, 
                $request->get('tableId'));
        } catch (\Exception $ex) {
            $app['session']->getFlashBag()->add('danger', 
                    "Cette table n'existe pas dans la base.");
            
            return $app->redirect(
                $app['url_generator']->generate('homepage')
            );
        }
        
        if (!_empty($data)){
            //On récupère le nom des colonnes de la table
            
            $header = array_keys(reset($data));
            
            $refChampsCle = array_flip(explode(',', $refChampsCle));
            
            foreach ($data as $key => $value) {

                $temp = array_intersect_key(
                    $value, 
                    $refChampsCle 
                );
                
                $cles[$key] = implode('|||', $temp);
            }
    }
        
        //On retourne le résultat dans le template 'AffichageTableSelectionnee.html.twig
        //On sépare les colonnes des données, le nom des colonne sera dans $header et 
        //les données contenu dans la table seront dans $data
    
        return $app['twig']->render(
            'ListeTable.html.twig', 
              array (
                'header'=> $header,
                'data' => $data,
                'cles' => $cles, 
                'tables'=>$request->get('tableId'),
            )    
        );

    }

    
/******************************************************************************/    

    
    public function deleteAction(Request $request, Application $app)
    {
        /*
         * On initialise TableManager.
         */
        
        $tableManager = ClassFactory::getManager('TableManager');
        
        /*
         * On va chercher le nom des champs clés de la table.
         */
        
        $refChampsCle = $tableManager->findOne(
            array(
                'REF_NOMTABLE' => $request->get('tableId'),
            ),
            'REF_CHAMPS_CLE'
        );
        
        /*
         * On transforme les variables en tableau afin de pouvoir les traiter.
         */
        
        $NomChampsCle = explode(',', $refChampsCle);
        
        $ValeurChampsCles = explode('|||', $request->get('pk'));
        
        /*
         * On combine les deux tableaux crée au dessus afin de pouvoir avoir un 
         * tableau avec les valeurs et les clés souhaitées.
         */
        
        $contrainte = array_combine(
            $NomChampsCle, $ValeurChampsCles
        );
        
        /*
         *On utilise le manager suivant, pour faire la requête de suppression,
         * avec le tableau de contrainte et le nom de la table en paramètre.
         */
        
        $manager = new \Models\Managers\TableManager($app['db']);
        
        $manager->delete($contrainte, $request->get('tableId'));
        
          /*
         * Redirection vers la liste des coefficients
         */
         /********************************************************/
        
        $app['session']->getFlashBag()->add('success', 
                    'Les informations ont bien été supprimées');
        
        return $app->redirect(
                $app['url_generator']->generate(
                    'liste',
                    array(
                        'tableId' => $request->get('tableId'),
                    )
                )
            );
    }
    
    
/******************************************************************************/
    
        
    public function createAction(Request $request, Application $app)
    {
        // NB
        // $request->get('tableId');

        // Etape 1 créer un tableau key => nom des colonnes.
        
        $manager = ClassFactory::getManager('TableManager');
        
        $row = $manager->selectRowTable($request->get('tableId'));
        $row = array_fill_keys(array_keys($row), null);
        /*
         * Appel de la fonction de mise à jour.
         */
        
        return $this->_majAction($request, $app, $row, $manager, 'CreateTable.html.twig');
        
    }

/******************************************************************************/
      

    Public function updateAction (Request $request, Application $app)
    { 
        $manager = ClassFactory::getManager('TableManager');
                
        $refChampsCle = $manager->findOne(
            array(
                'REF_NOMTABLE' => $request->get('tableId'),
            ),
            'REF_CHAMPS_CLE'
        );
        
        /*
         * On transforme $refChampsCle en tableau.
         */
        
        $NomChampsCle = explode(',', $refChampsCle);
        
        /*
         * On stocke la valeur sélectionner pour effectuer la selection.
         */
        
        $donnee = $request->get('valueUpdate');
        
        /*
         * On tranforme $donnee en tableau.
         */
        
        $tabDonnees = explode('|||', $donnee);
        
        /*
         * On combine les deux variable en un tableau afin de pouvoir s'en servir 
         * par la suite.
         */
        
        $selectRow=array_combine($NomChampsCle, $tabDonnees);
        $selectRow = $manager->selectRowTable($request->get('tableId'), $selectRow);
        /*
         * Appel de la fonction de mise à jour.
         */
        
        return $this->_majAction($request, $app, $selectRow, $manager, 'UpdateTable.html.twig');
        
    }
  

    private function _majAction ( Request $request,
                              Application $app,
                              $data,
                              $manager,
                              $template = '')
    {
    
     $donnee = $request->get('valueUpdate');
               
     $keys = array_keys($data);

        /*
         * On défnit d'abord la méthode qui va être utilisée puis
         * on prend le nom des colonnes de la tables.
         * Si l'action est create (methode POST) on met la valeur des champs a null.
         * La méthode utilisée va servir de paramètre pour les actions à effectuer.
         */

        /* 
         * Etape 2 Créer le formulaire et lui passer le tableau avec le nom des
         * colonnes et les données de la ligne sélectionnée en paramètre.
         *
         * Création du formulaire
         * ATTENTION : ajouter les options => array('method' => $request->getMethod())
         * pour les requêtes PUT
         ********************************************************/
             
        $form = $app['form.factory']->create(
            new \Forms\CreateForm($keys), 
            $data, 
            array (
                'method' => $request->getMethod(),
            )
        );
    
        // Etape 3 on merge le form et la requête
        $form->handleRequest($request);
                      
        // Etape 4 On test si le form est ok (si posté & valide)
        if ($form->isValid()) {
                        
            $manager->saveData($request->get('tableId'), $form->getData(), $valeurAutoIncr);
            /*
             * On regarde quels champs ont été modifiés afin ne faire l'update
             * que sur les champs ayant changé de valeur.
             * Si l'action est update on filte afin que seul les champs modifiées
             * soient dans l'update.

             */
            /*
             * Message prévenant que la modification ou l'insertion c'est bien passée.
             */
            
            $app['session']->getFlashBag()->add('success', 
                    'L\'enregistrement a bien été effectué');
            
            
            /*
             * Etape 7 Rediriger user vers l'affichage de la table.
             */
            return $app->redirect(
                $app['url_generator']->generate(
                    'liste',
                    array(
                        'tableId' => $request->get('tableId'),
                    )
                )
            );
        }
                
        /*
         * On envoie les informations dans le template.
         */
        
        return $app['twig']->render(
                $template, 
                  array (
                      'donnees'=>$donnee,
                      'tables'=>$request->get('tableId'),
                      'form' => $form->createView(),
                )    
            );
    }
/******************************************************************************/
}