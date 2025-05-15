const datiLivello1 = {
    name: "Livello 1: L'inizio di tutto",
    world: { width: 1600, height: 600 },
    playerStart: { x: 50, y: 50 },
    platforms: [
        { x: 100, y: 580, width: 200, height: 20, color: 'green', goal: false },
        { x: 350, y: 450, width: 150, height: 20, color: 'DodgerBlue', goal: false, isMoving: true, moveSpeed: 1, direction: 1, startX: 350, moveRangeX: 100 },
        { x: 50,  y: 350, width: 100, height: 20, color: 'purple', goal: false },
        { x: 600, y: 500, width: 180, height: 20, color: 'teal', goal: false},
        { x: 900, y: 400, width: 150, height: 20, color: 'pink', goal: false},
        { x: 1200, y: 300, width: 250, height: 20, color: 'brown', goal: true}
    ]
};