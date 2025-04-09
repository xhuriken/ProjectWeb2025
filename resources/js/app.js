import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

//Il n'a pas l'air de se charger quand je le met ici (pourtant j'ai toujours fait comme ça)
//Js for manage generation, (used in cohort > show.blade.php)
import './generationManager';
