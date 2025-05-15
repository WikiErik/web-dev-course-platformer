const datiLivello2 = {
    name: "Livello 2: Quando il gioco si fa duro, i quadrati giocano",
    world: { width: 2000, height: 600 },
    playerStart: { x: 30, y: 100 },
    platforms: [
        { x: 150, y: 550, width: 250, height: 20, color: 'darkgreen', goal: false },
        { x: 500, y: 400, width: 100, height: 20, color: 'orange', goal: false },
        { x: 800, y: 300, width: 120, height: 20, color: 'magenta', isMoving: true, moveSpeed: 1.5, direction: 1, startX: 800, moveRangeX: 150 },
        { x: 1800, y: 200, width: 100, height: 20, color: 'gold', goal: true }
    ]
};