'use strict';

let canvas = null;
let ctx = null;
let gameOverTextElement = null;

// Variabili Globali del Gioco
let world = { width: 800, height: 600 }; // Saranno sovrascritte da datiLivelloX.world
let camera = { x: 0, y: 0, width: 800, height: 600 }; // Valori di default, aggiornati dopo assegnazione canvas
let player = {
    x: 50, y: 50, width: 40, height: 40, color: 'blue',
    speed: 4, dx: 0, velocityY: 0, gravity: 0.6,
    jumpStrength: 13, isGrounded: false, coins: 0
};
let currentLevelPlatforms = [];
let currentLevelId = null;

let gameState = "loading";
let startTime = 0;
let finalTime = 0;
let displayTimeSeconds = 0;
let animationFrameId = null;

const keysPressed = {};

console.log("gioco.js: Script caricato. Variabili globali definite (elementi DOM non ancora assegnati).");

// Inizializzazione del Gioco e Caricamento Livello
function initializeLevel() {
    console.log(">>> initializeLevel() chiamata. LEVEL_TO_LOAD:", typeof LEVEL_TO_LOAD, LEVEL_TO_LOAD);

    let selectedLevelData = null;
    currentLevelId = null;

    if (typeof LEVEL_TO_LOAD === 'number') {
        console.log("initializeLevel: LEVEL_TO_LOAD è un numero.");
        if (LEVEL_TO_LOAD === 1 && typeof datiLivello1 !== 'undefined') {
            selectedLevelData = datiLivello1;
            currentLevelId = 1;
            console.log("initializeLevel: Dati per Livello 1 selezionati.");
        } else if (LEVEL_TO_LOAD === 2 && typeof datiLivello2 !== 'undefined') {
            selectedLevelData = datiLivello2;
            currentLevelId = 2;
            console.log("initializeLevel: Dati per Livello 2 selezionati.");
        } else {
            console.warn("initializeLevel: Nessun dato di livello corrispondente per LEVEL_TO_LOAD:", LEVEL_TO_LOAD, "o datiLivelloX non definiti.");
        }
    } else {
        console.error("initializeLevel: LEVEL_TO_LOAD non è un numero. Valore:", LEVEL_TO_LOAD, "Tipo:", typeof LEVEL_TO_LOAD);
    }

    if (!selectedLevelData) {
        console.error("initializeLevel: FALLIMENTO - selectedLevelData è null. Impossibile inizializzare.");
        if (gameOverTextElement) gameOverTextElement.textContent = "Errore: Impossibile caricare i dati del livello.";
        gameState = "error";
        return false;
    }

    console.log(`initializeLevel: Caricamento dati per: ${selectedLevelData.name}`);

    world.width = selectedLevelData.world.width;
    world.height = selectedLevelData.world.height;
    console.log("initializeLevel: Dimensioni mondo impostate:", world);

    currentLevelPlatforms = JSON.parse(JSON.stringify(selectedLevelData.platforms));
    currentLevelPlatforms.forEach(p => {
        if (p.isCoin) {
            p.collected = false;
        }
        if (p.isMoving && typeof p.startX !== 'undefined') {
            p.x = p.startX;
            p.direction = p.originalDirection || 1; // Assicurati che originalDirection sia definito o usa un default
        }
    });
    console.log("initializeLevel: Piattaforme del livello copiate e resettate:", currentLevelPlatforms.length, "piattaforme");
    
    player.x = selectedLevelData.playerStart.x;
    player.y = selectedLevelData.playerStart.y;
    player.dx = 0; player.velocityY = 0; player.isGrounded = false; player.coins = 0;
    console.log("initializeLevel: Giocatore resettato a x:", player.x, "y:", player.y);

    finalTime = 0; displayTimeSeconds = 0;
    for (const key in keysPressed) delete keysPressed[key];
    if (gameOverTextElement) gameOverTextElement.textContent = "";
    
    camera.x = 0; camera.y = 0;
    gameState = "initial";
    console.log("<<< initializeLevel: Livello inizializzato con successo. Stato:", gameState);
    return true;
}

function runGame() {
    console.log(">>> runGame() chiamata. Stato attuale:", gameState);
    if (gameState === "playing" || gameState === "error") {
        console.log("runGame: Uscita anticipata, stato:", gameState);
        return;
    }

    if (!initializeLevel()) {
        console.error("runGame: Fallimento inizializzazione livello. Il gioco non partirà.");
        return;
    }
    
    console.log("runGame: Chiamata a draw() per mostrare lo stato 'initial'.");
    draw();
    console.log("<<< runGame() terminata.");
}

// Event Listeners
window.addEventListener('keydown', function(event) {
    console.log("keydown evento: Tasto:", event.key, "Stato Gioco:", gameState);
    if (gameState === "initial") {
        gameState = "playing";
        startTime = performance.now();
        console.log("keydown: Gioco avviato! Stato: playing. Ora del timer:", startTime);
        if (animationFrameId) cancelAnimationFrame(animationFrameId);
        gameLoop();
    } else if (gameState === "playing") {
        keysPressed[event.key] = true;
        if ((event.key === 'ArrowUp' || event.key === 'w' || event.key === ' ') && player.isGrounded) {
            player.velocityY = -player.jumpStrength;
            player.isGrounded = false;
            console.log("keydown: Salto eseguito.");
        }
    } else if (gameState === "gameOver") {
        console.log("keydown: Rigioca richiesto dallo stato gameOver.");
        runGame();
    }
});

window.addEventListener('keyup', function(event) {
    // console.log("keyup evento: Tasto:", event.key, "Stato Gioco:", gameState);
    if (gameState === "playing") {
        delete keysPressed[event.key];
    }
});

// Collision Detection
function checkCollision(rect1, rect2) {
    return rect1.x < rect2.x + rect2.width &&
           rect1.x + rect1.width > rect2.x &&
           rect1.y < rect2.y + rect2.height &&
           rect1.y + rect1.height > rect2.y;
}

// Game Over e Salvataggio Punteggio
async function triggerGameOver(hasWon) {
    console.log(">>> triggerGameOver() chiamata. Vittoria:", hasWon, "Stato attuale:", gameState);
    if (gameState !== "playing") {
        console.log("triggerGameOver: Uscita anticipata, stato non è 'playing'.");
        return;
    }

    gameState = "gameOver";
    finalTime = (performance.now() - startTime) / 1000;
    displayTimeSeconds = finalTime;
    console.log("triggerGameOver: Calcolato finalTime:", finalTime);

    let message = "";
    if (hasWon) {
        message = `Hai VINTO! Tempo: ${finalTime.toFixed(2)}s. Monete: ${player.coins}. Premi un tasto per rigiocare.`;
        console.log(`triggerGameOver: Vittoria! Livello ID: ${currentLevelId}, Tempo: ${finalTime.toFixed(3)}, Monete: ${player.coins}`);

        if (USER_IS_LOGGED_IN && currentLevelId !== null) {
            console.log("triggerGameOver: Tentativo di salvataggio punteggio...");
            if (gameOverTextElement) gameOverTextElement.textContent = "Salvataggio punteggio...";
            try {
                const response = await fetch('../php/salvaPunteggio.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        levelId: currentLevelId,
                        timeSeconds: finalTime,
                        coinsCollected: player.coins
                    }),
                });
                const result = await response.json();
                console.log("triggerGameOver: Risposta da salvaPunteggio.php:", result);
                if (result.status === 'success') {
                    message += " (Punteggio Salvato!)";
                } else {
                    message += " (Errore salvataggio punteggio)";
                }
            } catch (error) {
                console.error("triggerGameOver: Errore di rete/fetch nel salvataggio:", error);
                message += " (Errore di rete nel salvataggio)";
            }
        }
    } else {
        message = `Hai PERSO! Tempo: ${finalTime.toFixed(2)}s. Premi un tasto per rigiocare.`;
        console.log(`triggerGameOver: Sconfitta! Livello ID: ${currentLevelId}, Tempo: ${finalTime.toFixed(3)}`);
    }

    if (gameOverTextElement) gameOverTextElement.textContent = message;
    draw();
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        console.log("triggerGameOver: Game loop fermato (animationFrameId cancellato).");
    }
    console.log("<<< triggerGameOver() terminata. Stato:", gameState);
}

// Update Function

function update() {
    if (gameState !== "playing" || !currentLevelPlatforms) {
        return;
    }

    // 1. GESTIONE INPUT E FISICA BASE GIOCATORE
    player.dx = 0;
    if (keysPressed['ArrowLeft'] || keysPressed['a']) player.dx = -player.speed;
    if (keysPressed['ArrowRight'] || keysPressed['d']) player.dx = player.speed;

    player.velocityY += player.gravity;

    // Posizioni target PREVISTE prima delle collisioni
    let oldPlayerY = player.y;
    let oldPlayerX = player.x;
    // Inizializza nextPlayerX/Y con la posizione corrente + il movimento intenzionale/fisica
    let nextPlayerX = player.x + player.dx;
    let nextPlayerY = player.y + player.velocityY;


    // 2. RACCOLTA MONETE
    // Usa un rettangolo basato sulla posizione attuale del giocatore per la collisione con le monete
    // Per semplicità, usiamo la posizione attuale + il movimento previsto solo per il test.
    let playerCollisionRectForCoins = { x: nextPlayerX, y: nextPlayerY, width: player.width, height: player.height };
    for (let i = 0; i < currentLevelPlatforms.length; i++) {
        const p = currentLevelPlatforms[i];
        if (p.isCoin && !p.collected && checkCollision(playerCollisionRectForCoins, p)) {
            player.coins += (p.value || 1);
            p.collected = true;
            console.log("Moneta raccolta! Totale:", player.coins);
        }
    }


    // 3. MOVIMENTO PIATTAFORME MOBILI E INTERAZIONE GIOCATORE
    let playerOnMovingPlatformData = null; // { platform: p, movementX: dx, movementY: dy }

    for (let i = 0; i < currentLevelPlatforms.length; i++) {
        const p = currentLevelPlatforms[i];
        if (p.isMoving && !p.isCoin) {
            let platformMovedX = 0;
            let platformMovedY = 0;

            if (p.moveAxis === 'y') { // Movimento Verticale
                let originalY = p.y;
                p.y += p.moveSpeed * p.direction;
                if ((p.direction === 1 && p.y >= p.startY + p.moveRangeY) || (p.direction === -1 && p.y <= p.startY)) {
                    p.direction *= -1;
                    p.y = (p.direction === 1) ? p.startY : p.startY + p.moveRangeY; // Correzione overshoot
                }
                platformMovedY = p.y - originalY;
            } else { // Movimento Orizzontale (default)
                let originalX = p.x;
                p.x += p.moveSpeed * p.direction;
                if ((p.direction === 1 && p.x >= p.startX + p.moveRangeX) || (p.direction === -1 && p.x <= p.startX)) {
                    p.direction *= -1;
                    p.x = (p.direction === 1) ? p.startX : p.startX + p.moveRangeX; // Correzione overshoot
                }
                platformMovedX = p.x - originalX;
            }

            // Verifica se il giocatore è SOPRA questa piattaforma e si stava muovendo con essa
            let playerRect = { x: oldPlayerX, y: oldPlayerY, width: player.width, height: player.height };
            let platformRectBeforeMove = { 
                x: (p.moveAxis === 'y' ? p.x : p.x - platformMovedX), // Posizione X originale della piattaforma
                y: (p.moveAxis === 'y' ? p.y - platformMovedY : p.y), // Posizione Y originale della piattaforma
                width: p.width, height: p.height 
            };

            // Se il giocatore era appoggiato (isGrounded era true a causa di questa piattaforma nel frame precedente)
            // E il centro del giocatore è entro i limiti orizzontali della piattaforma (prima del suo movimento)
            // E il fondo del giocatore era vicino alla cima della piattaforma (prima del suo movimento)
            if (player.isGrounded && // Controlla isGrounded dal frame precedente (logica più complessa)
                                     // O più semplicemente, se il giocatore è vicino alla superficie superiore
                (oldPlayerY + player.height >= platformRectBeforeMove.y - 2 && oldPlayerY + player.height <= platformRectBeforeMove.y + 5) && // Tolleranza verticale
                (oldPlayerX + player.width > platformRectBeforeMove.x && oldPlayerX < platformRectBeforeMove.x + platformRectBeforeMove.width) // Allineamento orizzontale
               ) {
                playerOnMovingPlatformData = { platform: p, movementX: platformMovedX, movementY: platformMovedY };
            }
        }
    }
    // Applica il movimento della piattaforma al giocatore, SE era su una di esse
    if (playerOnMovingPlatformData) {
        nextPlayerX += playerOnMovingPlatformData.movementX;
        nextPlayerY += playerOnMovingPlatformData.movementY; // Applica anche il movimento Y se la piattaforma si muove verticalmente
        // console.log("Giocatore mosso con piattaforma. DeltaX:", playerOnMovingPlatformData.movementX, "DeltaY:", playerOnMovingPlatformData.movementY);
    }


    // 4. COLLISIONI CON PIATTAFORME SOLIDE E APPLICAZIONE MOVIMENTO
    player.isGrounded = false; // Resetta prima di controllare le collisioni verticali
    let landedOnGoal = false;

    // COLLISIONE VERTICALE
    // Usa nextPlayerX (che potrebbe essere stato modificato dalla piattaforma mobile)
    // ma oldPlayerY per il controllo della direzione della collisione
    let tempPlayerRectY = { x: nextPlayerX, y: nextPlayerY, width: player.width, height: player.height };

    for (let i = 0; i < currentLevelPlatforms.length; i++) {
        const platform = currentLevelPlatforms[i];
        if (platform.isCoin || platform.collected) continue;

        if (checkCollision(tempPlayerRectY, platform)) {
            if (player.velocityY >= 0 && (oldPlayerY + player.height) <= platform.y + 1) { // Atterraggio
                nextPlayerY = platform.y - player.height;
                player.velocityY = 0;
                player.isGrounded = true;
                if (platform.goal) landedOnGoal = true;
                // Se atterra su una piattaforma mobile, il movimento con essa è già stato gestito
                // console.log("Atterrato su piattaforma:", platform.color, "Grounded:", player.isGrounded);
                break;
            }
            if (player.velocityY < 0 && oldPlayerY >= (platform.y + platform.height) -1 ) { // Testata contro soffitto
                nextPlayerY = platform.y + platform.height;
                player.velocityY = 0;
                // console.log("Collisione soffitto piattaforma:", platform.color);
                break;
            }
        }
    }
    player.y = nextPlayerY; // Applica movimento verticale finale


    // COLLISIONE ORIZZONTALE
    // Usa player.y (già aggiornata) e nextPlayerX
    let tempPlayerRectX = { x: nextPlayerX, y: player.y, width: player.width, height: player.height };
    for (let i = 0; i < currentLevelPlatforms.length; i++) {
        const platform = currentLevelPlatforms[i];
        if (platform.isCoin || platform.collected) continue;

        if (checkCollision(tempPlayerRectX, platform)) {
            // console.log("Collisione orizzontale rilevata con:", platform.color, "player.dx:", player.dx);
            if (player.dx > 0) { // Muovendosi a destra, ha colpito il lato sinistro della piattaforma
                nextPlayerX = platform.x - player.width;
                // console.log("Corretto a destra: nuova X", nextPlayerX);
            } else if (player.dx < 0) { // Muovendosi a sinistra, ha colpito il lato destro della piattaforma
                nextPlayerX = platform.x + platform.width;
                // console.log("Corretto a sinistra: nuova X", nextPlayerX);
            }
            // player.dx = 0; // Ferma il movimento intenzionale se c'è collisione
            break; 
        }
    }
    player.x = nextPlayerX; // Applica movimento orizzontale finale


    // 5. LIMITI DEL MONDO E FINE GIOCO
    if (player.x < 0) player.x = 0;
    if (player.x + player.width > world.width) player.x = world.width - player.width;

    if (landedOnGoal) {
        triggerGameOver(true);
        return;
    }

    // Controlla se è caduto FUORI dal mondo (un po' sotto il fondo per essere sicuri)
    if (player.y > world.height + player.height / 2) {
        triggerGameOver(false);
        return;
    }
    // Controlla se ha toccato il "soffitto" del mondo
    if (player.y < 0) {
        player.y = 0;
        player.velocityY = 0;
    }

    // 6. CAMERA
    // Centra la camera sul giocatore, con limiti ai bordi del mondo
    camera.x = player.x - (camera.width / 2) + (player.width / 2);
    // Limita la camera ai bordi del mondo
    if (camera.x < 0) camera.x = 0;
    if (camera.x + camera.width > world.width) camera.x = world.width - camera.width;
    // camera.y rimane 0 per lo scrolling solo orizzontale per ora
}

// Draw Function
function draw() {
    // console.log(">>> draw() chiamata. Stato:", gameState);
    if (!ctx) {
        console.error("draw(): ERRORE CRITICO - ctx non definito!");
        return;
    }
    ctx.clearRect(0, 0, camera.width, camera.height);

    if (currentLevelPlatforms) {
        for (let i = 0; i < currentLevelPlatforms.length; i++) {
            const p = currentLevelPlatforms[i];
            if (p.collected) continue;
            if (p.x + p.width > camera.x && p.x < camera.x + camera.width &&
                p.y + p.height > camera.y && p.y < camera.y + camera.height) {
                ctx.fillStyle = p.color;
                ctx.fillRect(p.x - camera.x, p.y - camera.y, p.width, p.height);
            }
        }
    } else {
        // console.warn("draw(): currentLevelPlatforms non definito o vuoto.");
    }

    ctx.fillStyle = player.color;
    ctx.fillRect(player.x - camera.x, player.y - camera.y, player.width, player.height);

    ctx.font = "20px Arial"; ctx.fillStyle = "black"; ctx.textAlign = "left"; ctx.textBaseline = "top";
    if (gameState === "playing" || gameState === "gameOver") {
        displayTimeSeconds = (gameState === "playing") ? (performance.now() - startTime) / 1000 : finalTime;
    } else {
        displayTimeSeconds = 0;
    }
    ctx.fillText(`Tempo: ${displayTimeSeconds.toFixed(2)}s`, 10, 10);
    ctx.fillText(`Monete: ${player.coins}`, 10, 35);

    if (gameState === "initial") {
        ctx.font = "30px Arial"; ctx.fillStyle = "rgba(0,0,0,0.8)";
        ctx.textAlign = "center"; ctx.textBaseline = "middle";
        ctx.fillText("Premi un tasto per iniziare!", camera.width / 2, camera.height / 2);
        // console.log("draw(): Disegnato messaggio 'Premi un tasto'.");
    }
}

// Game Loop
function gameLoop() {
    // console.log("gameLoop tick");
    if (gameState !== "playing") {
        if (animationFrameId) cancelAnimationFrame(animationFrameId);
        return;
    }
    update();
    draw();
    animationFrameId = requestAnimationFrame(gameLoop);
}

// Codice che serve per assicurarsi che il DOM sia completamente caricato
window.addEventListener('DOMContentLoaded', (event) => {
    console.log("DOM caricato. Avvio del gioco..."); 


    try {
        canvas = document.getElementById('gameCanvas');

        if (canvas) {
            ctx = canvas.getContext('2d');
            camera.width = canvas.width;
            camera.height = canvas.height;
        } else {
            console.error("ERRORE CRITICO: document.getElementById('gameCanvas') ha restituito null!");
        }

        gameOverTextElement = document.getElementById('GameOverText');

        if (!canvas || !ctx) {
            console.error("DOMContentLoaded: Canvas o ctx non validi. Interruzione runGame().");
            if(gameOverTextElement) gameOverTextElement.textContent = "Errore inizializzazione gioco: Canvas o contesto mancante.";
            else alert("Errore inizializzazione gioco: Canvas o contesto mancante."); // Fallback se gameOverTextElement non c'è
            return;
        }
        
        // Non chiamare runGame() ancora, vogliamo solo vedere i log
        console.log("DOMContentLoaded: Tutti i controlli preliminari superati. runGame() NON ancora chiamata.");

    } catch (e) {
        console.error("ERRORE FATALE DENTRO DOMContentLoaded:", e.message, e.stack);
        // alert("Errore fatale durante l'inizializzazione del gioco: " + e.message);
    }

     console.log("DOMContentLoaded: Chiamata a runGame().");
     runGame();
});