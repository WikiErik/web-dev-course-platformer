const datiLivello2 = {
    name: "Livello 2: L'Ascesa Insidiosa",
    world: { width: 2800, height: 600 },
    playerStart: { x: 40, y: 500 },
    platforms: [
        // Sezione 1: Inizio Tranquillo con Piattaforme Mobili Semplici
        { x: 0, y: 580, width: 200, height: 20, color: 'Sienna', goal: false }, // Partenza
        { x: 150, y: 540, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        { x: 250, y: 520, width: 120, height: 20, color: 'OliveDrab', isMoving: true, moveSpeed: 0.8, direction: 1, startX: 250, moveRangeX: 100 },
        { x: 300, y: 480, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 }, // Moneta sulla piattaforma mobile

        { x: 500, y: 480, width: 150, height: 20, color: 'Sienna', goal: false },
        { x: 550, y: 440, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        // Sezione 2: Salti tra Piattaforme più Piccole e una Mobile Verticale
        { x: 700, y: 450, width: 80, height: 20, color: 'DarkSlateGray', goal: false },
        { x: 820, y: 420, width: 80, height: 20, color: 'DarkSlateGray', goal: false },
        { x: 850, y: 380, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        { x: 950, y: 480, width: 60, height: 150, color: 'CadetBlue', goal: false, isMoving: true, moveSpeed: 1, direction: 1, startY: 150, moveRangeY: 230, moveAxis: 'y' },

        { x: 1050, y: 300, width: 100, height: 20, color: 'Sienna', goal: false }, // Atterraggio dopo la verticale
        { x: 1080, y: 260, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        // Sezione 3: Percorso Stretto e Piattaforme che Richiedono Precisione (ma fattibili)
        { x: 1200, y: 350, width: 50, height: 20, color: 'IndianRed', goal: false },
        { x: 1300, y: 380, width: 50, height: 20, color: 'IndianRed', goal: false },
        { x: 1400, y: 410, width: 50, height: 20, color: 'IndianRed', goal: false },
        { x: 1415, y: 370, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        { x: 1550, y: 380, width: 150, height: 20, color: 'Sienna', goal: false }, // Piattaforma sicura

        // Sezione 4: Piattaforme Mobili
        { x: 1750, y: 450, width: 100, height: 20, color: 'OliveDrab', isMoving: true, moveSpeed: 1.2, direction: 1, startX: 1750, moveRangeX: 150 },
        { x: 1860, y: 350, width: 60, height: 20, color: 'Peru', goal: false },
        { x: 1800, y: 410, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        { x: 1950, y: 350, width: 100, height: 20, color: 'OliveDrab', isMoving: true, moveSpeed: -0.9, direction: -1, startX: 1900, moveRangeX: 120 }, // Si muove in direzione opposta e più lenta
        { x: 1980, y: 310, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },

        // Sezione 5: Salita Finale con Monete Bonus
        { x: 2100, y: 300, width: 120, height: 20, color: 'Sienna', goal: false },
        { x: 2250, y: 250, width: 100, height: 20, color: 'DarkSlateGray', goal: false },
        { x: 2280, y: 210, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },
        { x: 2300, y: 210, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },


        { x: 2400, y: 200, width: 80, height: 20, color: 'Sienna', goal: false },
        { x: 2500, y: 150, width: 80, height: 20, color: 'Sienna', goal: false },
        { x: 2520, y: 110, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },
        { x: 2540, y: 110, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 },
        { x: 2560, y: 110, width: 20, height: 20, color: 'gold', isCoin: true, value: 1 }, // Fila di monete premio

        // Piattaforma Finale
        { x: 2650, y: 130, width: 120, height: 20, color: 'DarkGoldenRod', goal: true } // Obiettivo
    ]
};