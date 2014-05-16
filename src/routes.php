<?php 

use Symfony\Component\HttpFoundation\Response;

use Controllers\TableController;

/*
 * Mount routes
 **********************************************************/
$app->mount('/', new TableController());

/**********************************************************/



/*
 * Error handler
 **********************************************************/
$app->error(function (\Exception $e, $code) use ($app) {
    
    // commented for testing purposes
    //Si le debugger est activé, les messages du debugger seront utilisés.
    //Sinon il y aura une redirection vers les pages d'erreurs perso.
    if ($app['debug']) {
      return;
    }

    if ($code == 404) {

        return new Response( $app['twig']->render('errors/404.html.twig', array( 'message' => $e->getMessage() )), 404);
    }

    return new Response( $app['twig']->render('errors/500.html.twig', array( 'message' => $e->getMessage() )), 500);
});
/**********************************************************/
