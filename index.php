<?php

require __DIR__ . '/app/bootstrap.php';

use App\App;
use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\PublicController;
use App\Controllers\TeamPortalController;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

guard_setup();

Session::start(config('session'));

if (config('is_setup')) {
    App::get();
    View::share('appName', config('app.name'));
    View::share('csrf', Session::csrfToken());
}

$router = new Router();

// Public pages
$router->get('/', [PublicController::class, 'home']);
$router->get('/clasament', [PublicController::class, 'clasament']);
$router->get('/clasament/{grupa}', [PublicController::class, 'clasament']);
$router->get('/meciuri', [PublicController::class, 'meciuri']);
$router->get('/meci/{id}', [PublicController::class, 'meci']);
$router->get('/echipe', [PublicController::class, 'echipe']);
$router->get('/echipa/{id}', [PublicController::class, 'echipa']);
$router->get('/bracket', [PublicController::class, 'bracket']);
$router->get('/eliminatoare', [PublicController::class, 'bracket']);
$router->get('/istoric', [PublicController::class, 'istoric']);
$router->get('/sezoane', [PublicController::class, 'sezoane']);
$router->get('/sezoane/{id}', [PublicController::class, 'sezon']);

// Portal auto-administrare echipă (link securizat, fără login admin)
$router->get('/gestiune/{token}', [TeamPortalController::class, 'show']);
$router->post('/gestiune/{token}', [TeamPortalController::class, 'updateTeam']);
$router->post('/gestiune/{token}/jucatori', [TeamPortalController::class, 'storePlayer']);
$router->post('/gestiune/{token}/jucatori/{playerId}', [TeamPortalController::class, 'updatePlayer']);
$router->post('/gestiune/{token}/jucatori/{playerId}/delete', [TeamPortalController::class, 'deletePlayer']);

// API (compatibil cu trofeu-hub-serverside)
$router->post('/api/command', [ApiController::class, 'command']);
$router->get('/api/live', [ApiController::class, 'live']);
$router->get('/api/meci/{id}', [ApiController::class, 'matchLive']);

// Admin
$router->get('/admin/login', [AdminController::class, 'loginForm']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin/logout', [AdminController::class, 'logout']);
$router->get('/admin', [AdminController::class, 'dashboard']);

$router->get('/admin/echipe', [AdminController::class, 'teams']);
$router->post('/admin/echipe', [AdminController::class, 'teamStore']);
$router->post('/admin/echipe/{id}', [AdminController::class, 'teamUpdate']);
$router->post('/admin/echipe/{id}/delete', [AdminController::class, 'teamDelete']);
$router->post('/admin/echipe/{id}/link', [AdminController::class, 'teamEnsureLink']);
$router->post('/admin/echipe/{id}/link/regenerate', [AdminController::class, 'teamRegenerateLink']);

$router->get('/admin/jucatori', [AdminController::class, 'players']);
$router->post('/admin/jucatori', [AdminController::class, 'playerStore']);
$router->post('/admin/jucatori/{id}', [AdminController::class, 'playerUpdate']);
$router->post('/admin/jucatori/{id}/delete', [AdminController::class, 'playerDelete']);

$router->get('/admin/meciuri', [AdminController::class, 'matches']);
$router->post('/admin/meciuri', [AdminController::class, 'matchStore']);
$router->post('/admin/meciuri/{id}', [AdminController::class, 'matchUpdate']);
$router->post('/admin/meciuri/{id}/delete', [AdminController::class, 'matchDelete']);
$router->get('/admin/meciuri/{id}/panou', [AdminController::class, 'matchPanel']);
$router->post('/admin/meciuri/{id}/panou', [AdminController::class, 'matchPanelApi']);

$router->get('/admin/bracket', [AdminController::class, 'bracketAdmin']);
$router->post('/admin/bracket', [AdminController::class, 'bracketSave']);

$router->get('/admin/istoric', [AdminController::class, 'history']);
$router->post('/admin/istoric', [AdminController::class, 'historyStore']);
$router->post('/admin/istoric/{id}/delete', [AdminController::class, 'historyDelete']);

$router->get('/admin/setari', [AdminController::class, 'settingsForm']);
$router->post('/admin/setari', [AdminController::class, 'settingsSave']);

$router->get('/admin/sezoane', [AdminController::class, 'seasons']);
$router->post('/admin/sezoane/arhiveaza', [AdminController::class, 'seasonArchive']);
$router->post('/admin/sezoane/{id}/delete', [AdminController::class, 'seasonDelete']);

$router->get('/admin/exporturi', [AdminController::class, 'exports']);
$router->post('/admin/exporturi', [AdminController::class, 'exportGenerate']);
$router->post('/admin/exporturi/{id}/delete', [AdminController::class, 'exportDelete']);

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $uri);
