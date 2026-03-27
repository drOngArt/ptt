<?php

return [
    'adminRefreshTimer' => 6 * 1000, // n * 1000, where n equals number of seconds
    'judgeRefreshTimer' => 7 * 1000,
    'wallRefreshTimer' => 31 * 1000,
    'wallNoOfDances' => 49, // how many dances are dipslayed on wall
    'wallAllProgram' => false,    // shows all rounds
    'wallLines' => 43,       // how many lines are dipslayed on wall
    'wallLeftSite' => 45,    // proportion left to right side
    'judgeFinalRoundFontSize' => 'x-large',
    // classes list to manual modification, capital letter
    'classModifyResult' => [ 
        'R1', 'R2', 'R3', 'R4', 'BRAK',
        'G', 'G SOLO', 'G - SOLO', 'G-SOLO', 'G - DUETY', 'G-DUETY', 
        'H', 'H.', 'H SOLO', 'H - SOLO', 'H-SOLO', 'H - DUETY', 'H-DUETY',
        'ZŁOTO', 'ZŁOTO-SOLO', 'ZŁOTO-DUETY',
        'SREBRO', 'SREBRO-DUETY', 'SREBRO-SOLO',
        'BRĄZ', 'BRĄZ-DUETY', 'BRĄZ-SOLO'],
    // classes with places: 1 and 1 with honour
    'PositionRange_1withHonour' => [
        'ZŁOTO', 'ZŁOTO-SOLO', 'ZŁOTO-DUETY', 
        'SREBRO', 'SREBRO-DUETY', 'SREBRO-SOLO', 
        'BRĄZ', 'BRĄZ-DUETY', 'BRĄZ-SOLO', 'R1'],
    // classes with places 1-3
    'PositionRange_3' => [ 'G', 'G SOLO', 'G-SOLO', 'G - SOLO', 'G - DUETY', 'G-DUETY', 'R3' ],
    // classes with places 1-4
    'PositionRange_4' => ['R4'],
    // generate program with first round only
    'classOneRoundOnly' => ['SREBRO', 'BRĄZ', 'BRĄZ-DUETY', 'BRĄZ-SOLO', 'SREBRO-DUETY', 'SREBRO-SOLO'],
    // standard dances
    'stdDances' => ['WA', 'T', 'WW', 'VW', 'F', 'SL', 'Q'],
    // dance translation
    'replaceDance' => [
        'WA' => 'WALC ANGIELSKI',
        'T' => 'TANGO',
        'WW' => 'WALC WIEDEŃSKI',
        'F' => 'FOKSTROT',
        'Sl' => 'SLOWFOX',
        'SL' => 'SLOWFOX',
        'Q' => 'QUICKSTEP',
        'CC' => 'CHA CHA',
        'CH' => 'CHA CHA',
        'Ch' => 'CHA CHA',
        'S' => 'SAMBA',
        'Sa' => 'SAMBA',
        'Ss' => 'SALSA',
        'R' => 'RUMBA',
        'Pd' => 'PASO DOBLE',
        'PD' => 'PASO DOBLE',
        'J' => 'JIVE',
        'JV' => 'JIVE',
        'Pl' => 'POLKA',
        'PL' => 'POLKA',
        'Pol' => 'POLKA',
        'KR' => 'KRAKOWIAK',
        'Kr' => 'KRAKOWIAK',
        'Krk' => 'KRAKOWIAK',
        'Bl' => 'BLUES',
        'D' => 'DISCO',
        'RR' => 'Rock\'n\'Roll',
        'RnR' => 'Rock\'n\'Roll',
    ],
];
