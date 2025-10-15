<?php
return [
   'adminRefreshTimer' => 6 * 1000, //n * 1000, where n equals number of seconds
   'judgeRefreshTimer' => 7 * 1000,
   'wallRefreshTimer' => 23* 1000,
   'wallNoOfDances' => 46, //how many dances are dipslayed on wall
   'wallAllProgram' => false,    //shows all rounds
   'wallLines' => 31,       //how many lines are dipslayed on wall
   'wallLeftSite' => 50,    //proportion left to right side
   'judgeFinalRoundFontSize' => 'x-large',
   //classes list to manual modification, capital letter
   'classModifyResult' => [ 'H', 'H.', 'G', 'G.', 'G1', 'G2', 'G3', 'G4', 'R1', 'R2', 'R3', 'R4', 'BRAK',
   'G SOLO', 'G - SOLO', 'G - DUETY', 'G-SOLO', 'G-DUETY', 'G1 SOLO', 'G2 SOLO', 'G3 SOLO', 'H SOLO', 'H - SOLO',
   'H - DUETY', 'H-SOLO', 'H-DUETY', 'H1 SOLO', 'H2 SOLO', 'H1', 'H2', 'H3', 'H4', 'F1', 'F2', 'F3', 'F4',
   'ZŁOTO', 'ZŁOTO-SOLO',
   'SREBRO', 'SREBRO2', 'SREBRO3', 'SREBRO-DUETY', 'SREBRO-SOLO', 'SREBRO2-SOLO', 'SREBRO3-SOLO',
   'BRĄZ', 'BRĄZ2', 'BRĄZ3', 'BRĄZ-DUETY', 'BRĄZ-SOLO', 'BRĄZ2-SOLO', 'BRĄZ3-SOLO' ],
   //classes with places: 1 and 1 with honour
   'PositionRange_1withHonour' => [ 'BRĄZ', 'R1', 'BRĄZ-DUETY', 'BRĄZ-SOLO' ],
   //classes with places 1-3
   'PositionRange_3' => [ 'G', 'G.', 'G1', 'G2', 'G3',  'ZŁOTO', 'ZLOTO', 'R3', 
   'G SOLO', 'G1 SOLO', 'G2 SOLO', 'G3 SOLO', 'G-SOLO', 'G-DUETY', 
   'BRĄZ3', 'BRĄZ3-SOLO', 'SREBRO3-SOLO', 'SREBRO3', 'ZŁOTO-SOLO' ],
   //classes with places 1-4
   'PositionRange_4' => [ 'G4', 'R4', 'F4' ],
   //generate program with first round only
   'classOneRoundOnly' => [ 'SREBRO', 'BRĄZ', 'BRĄZ-DUETY', 'BRĄZ-SOLO', 'SREBRO-DUETY', 'SREBRO-SOLO'  ],
   //standard dances
   'stdDances' => [ 'WA', 'T', 'WW','VW', 'F', 'SL', 'Q' ],
   //dance translation
   'replaceDance' => [
      'WA' => 'WALC ANGIELSKI',
      'T'  => 'TANGO',
      'WW' => 'WALC WIEDEŃSKI',
      'F'  => 'FOKSTROT',
      'Sl' => 'SLOWFOX',
      'SL' => 'SLOWFOX',
      'Q'  => 'QUICKSTEP',
      'CC' => 'CHA CHA',
      'CH' => 'CHA CHA',
      'Ch' => 'CHA CHA',
      'S'  => 'SAMBA',
      'Sa' => 'SAMBA',
      'Ss' => 'SALSA',
      'R'  => 'RUMBA',
      'Pd' => 'PASO DOBLE',
      'PD' => 'PASO DOBLE',
      'J'  => 'JIVE',
      'JV' => 'JIVE',
      'Pl' => 'POLKA',
      'PL' => 'POLKA',
      'Pol'=> 'POLKA',
      'KR' => 'KRAKOWIAK',
      'Kr' => 'KRAKOWIAK',
      'Krk'=> 'KRAKOWIAK',
      'Bl' => 'BLUES',
      'D'  => 'DISCO',
      'RR' => 'Rock\'n\'Roll',
      'RnR'=> 'Rock\'n\'Roll'
      ]
];