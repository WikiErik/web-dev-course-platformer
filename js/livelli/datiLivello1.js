const datiLivello1 = {
    name: "Livello 1: L'inizio di tutto",
    world: { width: 1600, height: 600 },
    playerStart: { x: 50, y: 500 },
    platforms: [
        // Piattaforme esistenti
        { x: 0, y: 580, width: 300, height: 20, color: 'green', goal: false }, // Piattaforma di partenza più lunga
        { x: 350, y: 500, width: 150, height: 20, color: 'DodgerBlue', goal: false, isMoving: true, moveSpeed: 0.5, direction: 1, startX: 350, moveRangeX: 80 },
        { x: 250,  y: 420, width: 100, height: 20, color: 'purple', goal: false },
        { x: 600, y: 500, width: 180, height: 20, color: 'teal', goal: false},
        { x: 900, y: 400, width: 150, height: 20, color: 'pink', goal: false},
        { x: 1200, y: 300, width: 250, height: 20, color: 'brown', goal: true}, // Piattaforma obiettivo

        // --- MONETE per Livello 1 ---
        // Le monete avranno dimensioni più piccole, es. 20x20
        // Aggiungiamo la proprietà 'isCoin: true' e 'value: 1' (se vuoi monete con valori diversi)
        { x: 150, y: 540, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },
        { x: 270, y: 380, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 }, // Sulla piattaforma viola
        { x: 400, y: 460, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 }, // Sulla piattaforma mobile blu
        { x: 650, y: 460, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 }, // Sulla piattaforma teal
        { x: 950, y: 360, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 }, // Sulla piattaforma pink
    ]
};