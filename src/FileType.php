<?php
namespace AntonioPrimera\Artisan;

enum FileType: string
{
	case enum = 'Enum';
	case command = 'Command';
	case config = 'Config';
	case migration = 'Migration';
	case model = 'Model';
	case controller = 'Controller';
	case request = 'Request';
	case resource = 'Resource';
	case factory = 'Factory';
	case seeder = 'Seeder';
	case test = 'UnitTest';
	case feature = 'FeatureTest';
	case job = 'Job';
	case event = 'Event';
	case listener = 'Listener';
	case middleware = 'Middleware';
	case provider = 'Provider';
	case notification = 'Notification';
	case rule = 'Rule';
	case view = 'View';
	case viewComponent = 'ViewComponent';
	case viewModel = 'ViewModel';
	case routes = 'Routes';
	
	case js = 'Js';
	case css = 'Css';
	case lang = 'Lang';
	case vue = 'Vue';
	
}
