<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚓ Batalha Naval — Almirante ZEUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════
   RESET & VARIABLES
══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --navy:      #0a1628;
  --navy-mid:  #0d2040;
  --navy-light:#1a3a5c;
  --ocean:     #0e3a5a;
  --steel:     #2a4a6a;
  --cyan:      #00d4ff;
  --cyan-dim:  #0099bb;
  --amber:     #ffaa00;
  --red:       #ff3333;
  --green:     #00ff88;
  --white:     #e8f4ff;
  --gray:      #6a8aaa;
  --cell:      46px;
  --gap:       2px;
  --radius:    6px;
}

html { font-size: 15px; }
body {
  background: var(--navy);
  color: var(--white);
  font-family: 'Share Tech Mono', monospace;
  min-height: 100vh;
  overflow-x: hidden;
}

/* ══════════════════════════════════════════
   SCANLINE OVERLAY
══════════════════════════════════════════ */
body::before {
  content: '';
  position: fixed; inset: 0;
  background: repeating-linear-gradient(
    0deg,
    transparent,
    transparent 2px,
    rgba(0,0,0,0.08) 2px,
    rgba(0,0,0,0.08) 4px
  );
  pointer-events: none;
  z-index: 9999;
}

/* ══════════════════════════════════════════
   HEADER
══════════════════════════════════════════ */
header {
  background: linear-gradient(180deg, #061020 0%, var(--navy-mid) 100%);
  border-bottom: 2px solid var(--cyan);
  padding: 12px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 0 30px rgba(0,212,255,0.3);
}

.logo {
  font-family: 'Orbitron', monospace;
  font-size: 1.6rem;
  font-weight: 900;
  color: var(--cyan);
  text-shadow: 0 0 20px var(--cyan);
  letter-spacing: 3px;
}
.logo span { color: var(--amber); }

.status-bar {
  display: flex;
  gap: 20px;
  font-size: 0.8rem;
  color: var(--gray);
}
.status-item strong { color: var(--cyan); }

/* ══════════════════════════════════════════
   LAYOUT
══════════════════════════════════════════ */
.container {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  grid-template-rows: auto 1fr;
  gap: 12px;
  padding: 12px;
  max-width: 1400px;
  margin: 0 auto;
}

/* ══════════════════════════════════════════
   PANELS
══════════════════════════════════════════ */
.panel {
  background: linear-gradient(135deg, var(--navy-mid) 0%, var(--navy) 100%);
  border: 1px solid var(--navy-light);
  border-radius: var(--radius);
  padding: 14px;
}
.panel-title {
  font-family: 'Orbitron', monospace;
  font-size: 0.75rem;
  color: var(--cyan);
  letter-spacing: 2px;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--navy-light);
  text-transform: uppercase;
}

/* ══════════════════════════════════════════
   TABULEIROS
══════════════════════════════════════════ */
.board-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.board-label {
  font-family: 'Orbitron', monospace;
  font-size: 0.7rem;
  letter-spacing: 2px;
  text-transform: uppercase;
}
.board-label.enemy { color: var(--red); text-shadow: 0 0 10px var(--red); }
.board-label.mine   { color: var(--cyan); text-shadow: 0 0 10px var(--cyan); }

.board-wrapper {
  position: relative;
  user-select: none;
}

.board-grid {
  display: grid;
  grid-template-columns: 24px repeat(10, var(--cell));
  grid-template-rows:    24px repeat(10, var(--cell));
  gap: var(--gap);
}

.board-header {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  color: var(--gray);
  font-family: 'Orbitron', monospace;
}

.cell {
  width: var(--cell);
  height: var(--cell);
  background: var(--ocean);
  border: 1px solid var(--steel);
  border-radius: 2px;
  cursor: pointer;
  position: relative;
  transition: background 0.15s, transform 0.1s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

/* Enemy board cells */
.enemy-board .cell:hover:not(.shot):not(.disabled) {
  background: rgba(0, 212, 255, 0.25);
  border-color: var(--cyan);
  transform: scale(1.05);
  z-index: 1;
  box-shadow: 0 0 12px var(--cyan);
}

.cell.water  { background: var(--ocean); }
.cell.hit    { background: rgba(255,50,50,0.6);  border-color: var(--red); }
.cell.miss   { background: rgba(30,50,80,0.8);   border-color: var(--steel); }
.cell.sunk   { background: rgba(255,100,0,0.5);  border-color: var(--amber); }
.cell.ship   { background: rgba(0,180,255,0.3);  border-color: var(--cyan); }
.cell.ship-ia-hit { background: rgba(255,50,50,0.6); border-color: var(--red); }
.cell.shot   { cursor: default; }
.cell.disabled { cursor: not-allowed; opacity: 0.7; }

/* Placement mode */
.cell.placing-valid   { background: rgba(0,255,136,0.35); border-color: var(--green); }
.cell.placing-invalid { background: rgba(255,50,50,0.35); border-color: var(--red); }

/* Markers */
.marker {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  pointer-events: none;
}

/* ══════════════════════════════════════════
   CENTER PANEL
══════════════════════════════════════════ */
.center-panel {
  display: flex;
  flex-direction: column;
  gap: 14px;
  width: 260px;
}

/* ══════════════════════════════════════════
   SHIP SELECTOR
══════════════════════════════════════════ */
.ship-list { display: flex; flex-direction: column; gap: 8px; }

.ship-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border: 1px solid var(--navy-light);
  border-radius: 4px;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  background: rgba(255,255,255,0.02);
}
.ship-item:hover { border-color: var(--cyan-dim); background: rgba(0,212,255,0.05); }
.ship-item.selected { border-color: var(--cyan); background: rgba(0,212,255,0.12); box-shadow: 0 0 10px rgba(0,212,255,0.2); }
.ship-item.placed   { border-color: var(--green); opacity: 0.6; cursor: default; }
.ship-item.depleted { opacity: 0.4; cursor: default; }

.ship-preview {
  display: flex;
  gap: 3px;
}
.ship-sq {
  width: 14px; height: 14px;
  background: var(--cyan-dim);
  border: 1px solid var(--cyan);
  border-radius: 2px;
}

.ship-info { flex: 1; }
.ship-name { font-size: 0.75rem; color: var(--white); }
.ship-count { font-size: 0.65rem; color: var(--gray); }

/* Orientation toggle */
.orientation-btn {
  background: var(--navy-light);
  border: 1px solid var(--steel);
  border-radius: 4px;
  color: var(--cyan);
  padding: 6px 12px;
  cursor: pointer;
  font-family: 'Share Tech Mono', monospace;
  font-size: 0.75rem;
  width: 100%;
  transition: all 0.2s;
  text-align: center;
  margin-top: 6px;
}
.orientation-btn:hover { border-color: var(--cyan); background: rgba(0,212,255,0.1); }

/* ══════════════════════════════════════════
   BUTTONS
══════════════════════════════════════════ */
.btn {
  font-family: 'Orbitron', monospace;
  font-size: 0.7rem;
  letter-spacing: 1px;
  border: none;
  border-radius: 4px;
  padding: 10px 16px;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
  text-transform: uppercase;
}
.btn-primary {
  background: linear-gradient(135deg, var(--cyan-dim), var(--cyan));
  color: var(--navy);
  font-weight: 700;
}
.btn-primary:hover { box-shadow: 0 0 20px var(--cyan); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-danger {
  background: linear-gradient(135deg, #aa2200, var(--red));
  color: white;
}
.btn-danger:hover { box-shadow: 0 0 20px var(--red); }

.btn-ghost {
  background: transparent;
  border: 1px solid var(--navy-light);
  color: var(--gray);
}
.btn-ghost:hover { border-color: var(--gray); color: var(--white); }

/* ══════════════════════════════════════════
   CHAT
══════════════════════════════════════════ */
.chat-box {
  background: rgba(0,0,0,0.3);
  border: 1px solid var(--navy-light);
  border-radius: 4px;
  height: 180px;
  overflow-y: auto;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  scroll-behavior: smooth;
}
.chat-box::-webkit-scrollbar { width: 4px; }
.chat-box::-webkit-scrollbar-track { background: transparent; }
.chat-box::-webkit-scrollbar-thumb { background: var(--steel); border-radius: 2px; }

.chat-msg {
  font-size: 0.72rem;
  line-height: 1.5;
  padding: 6px 10px;
  border-radius: 4px;
  max-width: 92%;
}
.chat-msg.pergunta {
  background: rgba(0,212,255,0.1);
  border-left: 2px solid var(--cyan);
  align-self: flex-end;
  color: var(--white);
}
.chat-msg.resposta {
  background: rgba(255,100,0,0.1);
  border-left: 2px solid var(--amber);
  align-self: flex-start;
  color: var(--amber);
}
.chat-msg .who {
  font-size: 0.6rem;
  opacity: 0.6;
  margin-bottom: 3px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.chat-input-row {
  display: flex;
  gap: 6px;
  margin-top: 8px;
}
.chat-input {
  flex: 1;
  background: rgba(0,0,0,0.3);
  border: 1px solid var(--navy-light);
  border-radius: 4px;
  color: var(--white);
  font-family: 'Share Tech Mono', monospace;
  font-size: 0.72rem;
  padding: 8px;
  outline: none;
  transition: border-color 0.2s;
}
.chat-input:focus { border-color: var(--cyan); }
.chat-input::placeholder { color: var(--gray); }

.chat-send {
  background: var(--navy-light);
  border: 1px solid var(--steel);
  border-radius: 4px;
  color: var(--cyan);
  padding: 8px 14px;
  cursor: pointer;
  font-family: 'Share Tech Mono', monospace;
  font-size: 0.8rem;
  transition: all 0.2s;
  white-space: nowrap;
}
.chat-send:hover { border-color: var(--cyan); background: rgba(0,212,255,0.1); }

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
}
.stat-box {
  background: rgba(0,0,0,0.2);
  border: 1px solid var(--navy-light);
  border-radius: 4px;
  padding: 8px;
  text-align: center;
}
.stat-val {
  font-family: 'Orbitron', monospace;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--cyan);
}
.stat-lbl { font-size: 0.6rem; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; }

/* ══════════════════════════════════════════
   LOG
══════════════════════════════════════════ */
.log-box {
  background: rgba(0,0,0,0.25);
  border: 1px solid var(--navy-light);
  border-radius: 4px;
  height: 90px;
  overflow-y: auto;
  padding: 8px 10px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  scroll-behavior: smooth;
}
.log-entry {
  font-size: 0.68rem;
  color: var(--gray);
  line-height: 1.4;
}
.log-entry.jogador { color: var(--cyan-dim); }
.log-entry.ia      { color: var(--amber); }
.log-entry.afundou { color: var(--red); font-weight: bold; }
.log-entry.vitoria { color: var(--green); font-weight: bold; }

/* ══════════════════════════════════════════
   MODAL
══════════════════════════════════════════ */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.85);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
}
.modal-overlay.hidden { display: none; }

.modal {
  background: linear-gradient(135deg, var(--navy-mid), var(--navy));
  border: 2px solid var(--cyan);
  border-radius: 10px;
  padding: 32px;
  text-align: center;
  max-width: 380px;
  box-shadow: 0 0 60px rgba(0,212,255,0.4);
  animation: modalIn 0.4s ease;
}
@keyframes modalIn {
  from { transform: scale(0.8) translateY(-20px); opacity: 0; }
  to   { transform: scale(1) translateY(0);      opacity: 1; }
}
.modal-icon { font-size: 4rem; margin-bottom: 16px; display: block; }
.modal-title {
  font-family: 'Orbitron', monospace;
  font-size: 1.6rem;
  font-weight: 900;
  margin-bottom: 8px;
}
.modal-title.win  { color: var(--green); text-shadow: 0 0 20px var(--green); }
.modal-title.lose { color: var(--red);   text-shadow: 0 0 20px var(--red);   }
.modal-body { color: var(--gray); font-size: 0.82rem; line-height: 1.6; margin-bottom: 20px; }

/* ══════════════════════════════════════════
   TURN INDICATOR
══════════════════════════════════════════ */
.turn-indicator {
  text-align: center;
  padding: 8px;
  border-radius: 4px;
  font-family: 'Orbitron', monospace;
  font-size: 0.7rem;
  letter-spacing: 1px;
  transition: all 0.3s;
}
.turn-indicator.player-turn {
  background: rgba(0,212,255,0.1);
  border: 1px solid var(--cyan);
  color: var(--cyan);
}
.turn-indicator.ia-turn {
  background: rgba(255,50,50,0.1);
  border: 1px solid var(--red);
  color: var(--red);
}

/* ══════════════════════════════════════════
   PULSE ANIMATION
══════════════════════════════════════════ */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.5; }
}
.pulse { animation: pulse 1.2s ease infinite; }

/* ══════════════════════════════════════════
   LOADING
══════════════════════════════════════════ */
.loading-dots::after {
  content: '...';
  animation: dots 1.2s steps(4, end) infinite;
}
@keyframes dots {
  0%,20%  { content: '.'; }
  40%     { content: '..'; }
  60%,100%{ content: '...'; }
}

/* ══════════════════════════════════════════
   ANIMAÇÕES DE AFUNDAMENTO E EFEITOS
══════════════════════════════════════════ */

/* Célula piscando antes de afundar */
@keyframes sinkFlash {
  0%,100% { background: rgba(255,80,0,0.9);  box-shadow: 0 0 18px #ff5000; }
  50%      { background: rgba(255,200,0,0.7); box-shadow: 0 0 30px #ffcc00; }
}
.cell.sinking { animation: sinkFlash 0.18s ease infinite; z-index: 2; }

/* Célula sumindo */
@keyframes sinkOut {
  0%   { transform: scale(1);    opacity: 1; background: rgba(255,50,0,0.8); }
  60%  { transform: scale(1.15); opacity: 0.6; }
  100% { transform: scale(0);    opacity: 0; background: rgba(0,30,60,0.9); }
}
.cell.sunk-anim { animation: sinkOut 0.4s ease forwards; }

/* Marcador de tiro — entrada suave */
@keyframes markerIn {
  from { transform: scale(0) rotate(-180deg); opacity: 0; }
  to   { transform: scale(1) rotate(0deg);   opacity: 1; }
}
.marker { animation: markerIn 0.25s cubic-bezier(0.34,1.56,0.64,1) forwards; }

/* Onda de choque no acerto */
@keyframes shockwave {
  0%   { transform: scale(0.5); opacity: 0.8; border-width: 3px; }
  100% { transform: scale(2.5); opacity: 0;   border-width: 1px; }
}
.cell.shockwave::after {
  content: '';
  position: absolute;
  inset: 0;
  border: 2px solid var(--red);
  border-radius: 50%;
  animation: shockwave 0.5s ease-out forwards;
  pointer-events: none;
}

/* Partículas de splash */
@keyframes splash {
  0%   { transform: translate(0,0) scale(1); opacity: 1; }
  100% { transform: translate(var(--sx), var(--sy)) scale(0); opacity: 0; }
}
.splash-particle {
  position: fixed;
  width: 6px; height: 6px;
  border-radius: 50%;
  pointer-events: none;
  z-index: 5000;
  animation: splash 0.6s ease-out forwards;
}

/* Flash de tela no afundamento */
@keyframes screenFlash {
  0%,100% { opacity: 0; }
  10%,30% { opacity: 1; }
}
#screen-flash {
  position: fixed; inset: 0;
  pointer-events: none;
  z-index: 4000;
  background: radial-gradient(circle, rgba(255,100,0,0.35) 0%, transparent 70%);
  opacity: 0;
}
#screen-flash.active { animation: screenFlash 0.5s ease; }

/* Texto flutuante de resultado */
@keyframes floatUp {
  0%   { transform: translateY(0)  scale(0.8); opacity: 1; }
  100% { transform: translateY(-80px) scale(1.1); opacity: 0; }
}
.float-text {
  position: fixed;
  font-family: 'Orbitron', monospace;
  font-weight: 900;
  font-size: 1.4rem;
  pointer-events: none;
  z-index: 5000;
  text-shadow: 0 0 20px currentColor;
  animation: floatUp 1s ease forwards;
}
.float-text.hit    { color: #ff4400; }
.float-text.miss   { color: #0099bb; }
.float-text.sunk   { color: #ffaa00; }
.float-text.win    { color: #00ff88; font-size: 2rem; }
.float-text.defeat { color: #ff3333; font-size: 2rem; }

/* Ondas no fundo durante batalha */
@keyframes waveBg {
  0%   { background-position: 0 0; }
  100% { background-position: 60px 0; }
}
body.battle-active {
  background-image: repeating-linear-gradient(
    90deg,
    transparent 0px, transparent 28px,
    rgba(0,100,150,0.04) 28px, rgba(0,100,150,0.04) 30px
  );
  animation: waveBg 3s linear infinite;
}

/* Pulso no painel de turno da IA */
@keyframes radarSpin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.radar-icon {
  display: inline-block;
  margin-right: 6px;
}
.ia-turn .radar-icon { animation: radarSpin 1s linear infinite; }

/* Modal melhorado com partículas */
@keyframes confetti {
  0%   { transform: translateY(-20px) rotate(0deg);   opacity: 1; }
  100% { transform: translateY(120px) rotate(720deg); opacity: 0; }
}
.confetti-piece {
  position: absolute;
  width: 8px; height: 8px;
  border-radius: 2px;
  animation: confetti 1.2s ease-in forwards;
  pointer-events: none;
}

/* ══════════════════════════════════════════
   ANIMAÇÃO DE ENTRADA
══════════════════════════════════════════ */

/* Tela de intro que cobre tudo */
#intro-overlay {
  position: fixed; inset: 0;
  z-index: 9000;
  background: var(--navy);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0;
  pointer-events: all;
}

/* Logo descendo do topo */
@keyframes logoDescendo {
  0%   { transform: translateY(-120px); opacity: 0; }
  60%  { transform: translateY(10px);   opacity: 1; }
  100% { transform: translateY(0);      opacity: 1; }
}
.intro-logo {
  font-family: 'Orbitron', monospace;
  font-size: 3.5rem;
  font-weight: 900;
  color: var(--cyan);
  text-shadow: 0 0 40px var(--cyan), 0 0 80px rgba(0,212,255,0.4);
  letter-spacing: 6px;
  animation: logoDescendo 1s cubic-bezier(0.34,1.4,0.64,1) forwards;
  opacity: 0;
}
.intro-logo span { color: var(--amber); }

.intro-sub {
  font-size: 0.9rem;
  color: var(--gray);
  letter-spacing: 4px;
  margin-top: 8px;
  animation: logoDescendo 1s 0.2s cubic-bezier(0.34,1.2,0.64,1) forwards;
  opacity: 0;
}

/* Linha divisória animada */
@keyframes linhaExpandindo {
  from { width: 0; opacity: 0; }
  to   { width: 340px; opacity: 1; }
}
.intro-linha {
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--cyan), var(--amber), var(--cyan), transparent);
  margin: 20px 0;
  animation: linhaExpandindo 0.8s 0.6s ease forwards;
  width: 0; opacity: 0;
}

/* Tabuleiros emergindo do mar */
@keyframes emergeLeft {
  0%   { transform: translateX(-160px) translateY(60px); opacity: 0; }
  70%  { transform: translateX(8px)    translateY(-4px); opacity: 1; }
  100% { transform: translateX(0)      translateY(0);    opacity: 1; }
}
@keyframes emergeRight {
  0%   { transform: translateX(160px)  translateY(60px); opacity: 0; }
  70%  { transform: translateX(-8px)   translateY(-4px); opacity: 1; }
  100% { transform: translateX(0)      translateY(0);    opacity: 1; }
}
@keyframes emergeCenter {
  0%   { transform: translateY(80px); opacity: 0; }
  70%  { transform: translateY(-6px); opacity: 1; }
  100% { transform: translateY(0);    opacity: 1; }
}

/* Mini tabuleiros animados na intro */
.intro-boards {
  display: flex;
  gap: 24px;
  align-items: center;
  margin: 10px 0 6px;
}
.intro-board-left  { animation: emergeLeft   0.9s 0.9s cubic-bezier(0.34,1.2,0.64,1) forwards; opacity: 0; }
.intro-board-right { animation: emergeRight  0.9s 1.1s cubic-bezier(0.34,1.2,0.64,1) forwards; opacity: 0; }
.intro-vs          { animation: emergeCenter 0.9s 1.0s cubic-bezier(0.34,1.5,0.64,1) forwards; opacity: 0;
                     font-family: 'Orbitron', monospace; font-size: 1.6rem; font-weight: 900;
                     color: var(--amber); text-shadow: 0 0 20px var(--amber); }

/* Mini grid decorativo */
.mini-grid {
  display: grid;
  grid-template-columns: repeat(5, 14px);
  grid-template-rows:    repeat(5, 14px);
  gap: 2px;
}
.mini-cell {
  background: var(--ocean);
  border: 1px solid var(--steel);
  border-radius: 1px;
  transition: background 0.2s;
}
.mini-cell.ship  { background: var(--cyan-dim); border-color: var(--cyan); }
.mini-cell.hit   { background: rgba(255,50,0,0.7); }
.mini-cell.water { background: rgba(0,80,120,0.4); }

/* Texto "CLIQUE PARA INICIAR" piscando */
@keyframes blink {
  0%,100% { opacity: 1; } 50% { opacity: 0.2; }
}
.intro-start {
  font-family: 'Orbitron', monospace;
  font-size: 0.8rem;
  letter-spacing: 3px;
  color: var(--cyan);
  text-transform: uppercase;
  animation: blink 1.2s ease infinite;
  margin-top: 16px;
  cursor: pointer;
}

/* Fade-out do overlay */
@keyframes overlayOut {
  0%   { opacity: 1; transform: scale(1); }
  100% { opacity: 0; transform: scale(1.04); }
}
#intro-overlay.saindo {
  animation: overlayOut 0.7s ease forwards;
  pointer-events: none;
}

/* Conteúdo principal começa invisível */
#main-content {
  opacity: 0;
  transition: opacity 0.5s ease;
}
#main-content.visivel { opacity: 1; }

/* Header entra por cima */
@keyframes headerIn {
  from { transform: translateY(-100%); opacity: 0; }
  to   { transform: translateY(0);     opacity: 1; }
}
header { animation: none; }
header.animando { animation: headerIn 0.6s 0.2s ease forwards; opacity: 0; }

/* Tabuleiros sobem */
.board-section {
  transition: transform 0.6s cubic-bezier(0.34,1.2,0.64,1), opacity 0.6s ease;
}
.board-section.entrada-esq { transform: translateX(-80px); opacity: 0; }
.board-section.entrada-dir { transform: translateX(80px);  opacity: 0; }
.board-section.entrada-ctr { transform: translateY(60px);  opacity: 0; }

/* ══════════════════════════════════════════
   AVATAR DO ALMIRANTE ZEUS
══════════════════════════════════════════ */

.zeus-avatar-wrap {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 4px;
}

.zeus-avatar {
  width: 52px;
  height: 52px;
  flex-shrink: 0;
  image-rendering: pixelated;
  border: 2px solid var(--amber);
  border-radius: 4px;
  background: var(--navy-mid);
  transition: border-color 0.3s;
  overflow: hidden;
}
.zeus-avatar.raiva   { border-color: var(--red);   box-shadow: 0 0 10px var(--red); }
.zeus-avatar.alegria { border-color: var(--green);  box-shadow: 0 0 10px var(--green); }
.zeus-avatar.neutro  { border-color: var(--amber);  box-shadow: 0 0 8px var(--amber); }
.zeus-avatar.espanto { border-color: var(--cyan);   box-shadow: 0 0 10px var(--cyan); }

/* Animação de expressão mudando */
@keyframes avatarPulse {
  0%,100% { transform: scale(1); }
  50%     { transform: scale(1.08); }
}
.zeus-avatar.mudando { animation: avatarPulse 0.3s ease; }

.zeus-msg-wrap { flex: 1; }

/* ══════════════════════════════════════════
   RESPONSIVE TWEAKS
══════════════════════════════════════════ */
@media (max-width: 1280px) {
  :root { --cell: 40px; }
  .center-panel { width: 240px; }
  .container { gap: 10px; padding: 10px; }
}

@media (max-width: 1050px) {
  :root { --cell: 34px; }
  .center-panel { width: 220px; }
  .container { gap: 8px; padding: 8px; }
}

@media (max-width: 900px) {
  :root { --cell: 30px; }
  .container {
    grid-template-columns: 1fr;
    grid-template-rows: auto;
  }
  .center-panel { width: 100%; }
}

</style>
</head>
<body>

<!-- Flash de tela para afundamentos -->
<div id="screen-flash"></div>

<!-- ══ INTRO OVERLAY ══ -->
<div id="intro-overlay">
  <div class="intro-logo">⚓ BATALHA <span>NAVAL</span></div>
  <div class="intro-sub">ALMIRANTE ZEUS AGUARDA SUA DERROTA</div>
  <div class="intro-linha"></div>
  <div class="intro-boards">
    <div class="intro-board-left">
      <div class="mini-grid" id="mini-left"></div>
    </div>
    <div class="intro-vs">VS</div>
    <div class="intro-board-right">
      <div class="mini-grid" id="mini-right"></div>
    </div>
  </div>
  <div class="intro-start" id="intro-start" onclick="fecharIntro()">▶ CLIQUE PARA INICIAR</div>
</div>

<div id="main-content">

<!-- HEADER -->
<header>
  <div class="logo">⚓ BATALHA <span>NAVAL</span></div>
  <div class="status-bar">
    <div class="status-item">ALMIRANTE: <strong>ZEUS</strong></div>
    <div class="status-item" id="hdr-status">AGUARDANDO</div>
    <div class="status-item" id="hdr-tiros">TIROS: <strong>0</strong></div>
    <button class="sound-btn on" id="btn-musica" onclick="toggleMusica()" title="Ligar/Desligar música">🎵 MÚSICA</button>
  </div>
</header>

<!-- MODAL FIM DE JOGO -->
<div class="modal-overlay hidden" id="modal-fim">
  <div class="modal">
    <span class="modal-icon" id="modal-icon">🏆</span>
    <div class="modal-title" id="modal-title">VITÓRIA!</div>
    <div class="modal-body" id="modal-body">Você derrotou o Almirante ZEUS!</div>
    <button class="btn btn-primary" onclick="novaPartida()">⚓ NOVA BATALHA</button>
  </div>
</div>

<!-- MAIN CONTAINER -->
<div class="container">

  <!-- TABULEIRO DO JOGADOR -->
  <div class="board-section">
    <div class="board-label mine">◀ SUA FROTA</div>
    <div class="board-wrapper">
      <div class="board-grid" id="my-board"></div>
    </div>
    <div style="font-size:0.65rem; color:var(--gray); margin-top:4px;">Seu tabuleiro — defenda-o!</div>
  </div>

  <!-- PAINEL CENTRAL -->
  <div class="center-panel">

    <!-- FASE: POSICIONAMENTO -->
    <div id="phase-setup">
      <div class="panel">
        <div class="panel-title">📍 POSICIONAR FROTA</div>
        <div class="ship-list" id="ship-list"></div>
        <button class="orientation-btn" onclick="toggleOrientation()">
          🔄 GIRAR: <span id="ori-label">HORIZONTAL</span>
        </button>
        <div style="margin-top:10px; display:flex; gap:6px;">
          <button class="btn btn-ghost" style="font-size:0.65rem;" onclick="limparBarcos()">🗑 LIMPAR</button>
          <button class="btn btn-primary" id="btn-iniciar" onclick="iniciarBatalha()" disabled>
            ⚔ INICIAR!
          </button>
        </div>
      </div>
    </div>

    <!-- FASE: BATALHA -->
    <div id="phase-battle" style="display:none;">
      <div class="panel">
        <div class="panel-title">📊 SITUAÇÃO</div>
        <div class="turn-indicator player-turn" id="turn-indicator">
          🎯 SUA VEZ DE ATACAR
        </div>
        <div class="stats-grid" style="margin-top:10px;">
          <div class="stat-box">
            <div class="stat-val" id="stat-tiros-j">0</div>
            <div class="stat-lbl">Seus Tiros</div>
          </div>
          <div class="stat-box">
            <div class="stat-val" id="stat-acertos-j">0</div>
            <div class="stat-lbl">Acertos</div>
          </div>
          <div class="stat-box">
            <div class="stat-val" id="stat-tiros-i">0</div>
            <div class="stat-lbl">Tiros Inimigos</div>
          </div>
          <div class="stat-box">
            <div class="stat-val" id="stat-acertos-i">0</div>
            <div class="stat-lbl">Acertos Inimigos</div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-title">📋 LOG DE BATALHA</div>
        <div class="log-box" id="log-box"></div>
      </div>

      <button class="btn btn-danger" style="font-size:0.65rem;" onclick="if(confirm('Abandonar esta batalha?')) novaPartida()">
        🏳 RENDER-SE
      </button>
    </div>

    <!-- CHAT COM IA (sempre visível durante batalha) -->
    <div id="chat-panel" style="display:none;">
      <div class="panel">
        <div class="panel-title">💬 FALAR COM O ALMIRANTE</div>
        <!-- Avatar + primeira mensagem -->
        <div class="zeus-avatar-wrap">
          <canvas class="zeus-avatar neutro" id="zeus-avatar" width="52" height="52"></canvas>
          <div class="zeus-msg-wrap">
            <div class="chat-box" id="chat-box">
              <div class="chat-msg resposta">
                <div class="who">⚡ ALMIRANTE ZEUS</div>
                Bem-vindo aos mares do inferno, recruta! Posicione sua frota miserável... se tiver coragem!
              </div>
            </div>
          </div>
        </div>
        <div class="chat-input-row">
          <input class="chat-input" id="chat-input" type="text" placeholder="Provocar o Almirante..." maxlength="200">
          <button class="chat-send" onclick="enviarChat()">➤</button>
        </div>
      </div>
    </div>

  </div>

  <!-- TABULEIRO DO INIMIGO -->
  <div class="board-section">
    <div class="board-label enemy">FROTA INIMIGA ▶</div>
    <div class="board-wrapper">
      <div class="board-grid enemy-board" id="enemy-board"></div>
    </div>
    <div style="font-size:0.65rem; color:var(--gray); margin-top:4px;">Tabuleiro inimigo — ataque!</div>
  </div>

</div>

</div><!-- /main-content -->

<!-- ══════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════ -->
<script>
// ─────────────────────────────────────────
// ESTADO DO JOGO
// ─────────────────────────────────────────
const STATE = {
  partidaId:    null,
  fase:         'setup',   // 'setup' | 'battle' | 'over'
  meuTabuleiro: Array.from({length:10}, () => Array(10).fill(null)),
  tiros_j:      {},
  tiros_ia:     {},
  barcosSelecionado: null,
  horizontal:   true,
  barcoConfig: [
    {tipo:'submarino',        nome:'Submarino',        tamanho:1, qtd:2, restantes:2},
    {tipo:'contratorpedeiro', nome:'Contratorpedeiro', tamanho:2, qtd:2, restantes:2},
    {tipo:'cruzador',         nome:'Cruzador',         tamanho:3, qtd:1, restantes:1},
  ],
  meuTurn: true,
};

const LETRAS = ['A','B','C','D','E','F','G','H','I','J'];

// ─────────────────────────────────────────
// INIT
// ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  // Monta mini-grids decorativos do intro
  montarMiniGrids();
  // Pré-carrega o jogo em paralelo enquanto intro aparece
  iniciarJogo();
  document.getElementById('chat-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') enviarChat();
  });
  // Desenha avatar inicial
  zeusAvatar('neutro');
});

// ─────────────────────────────────────────
// INTRO OVERLAY
// ─────────────────────────────────────────
function montarMiniGrids() {
  const padraoL = [
    0,0,1,0,0,
    0,0,1,0,0,
    0,0,0,0,1,
    1,1,0,0,1,
    0,0,0,0,0,
  ];
  const padraoR = [
    0,1,1,1,0,
    0,0,0,0,0,
    0,1,0,0,0,
    0,1,0,0,0,
    0,0,0,1,1,
  ];
  ['mini-left','mini-right'].forEach((id, idx) => {
    const el = document.getElementById(id);
    if (!el) return;
    const pad = idx === 0 ? padraoL : padraoR;
    pad.forEach((v, i) => {
      const d = document.createElement('div');
      d.className = 'mini-cell' + (v ? ' ship' : '');
      el.appendChild(d);
    });
  });
  // Anima células do mini grid
  setTimeout(() => {
    document.querySelectorAll('#mini-left .mini-cell.ship').forEach((c,i) => {
      setTimeout(() => c.classList.add('hit'), 300 + i*120);
    });
    document.querySelectorAll('#mini-right .mini-cell.ship').forEach((c,i) => {
      setTimeout(() => c.classList.add('water'), 400 + i*100);
    });
  }, 1200);
}

function fecharIntro() {
  const overlay = document.getElementById('intro-overlay');
  const main    = document.getElementById('main-content');
  const header  = document.querySelector('header');

  overlay.classList.add('saindo');

  setTimeout(() => {
    overlay.style.display = 'none';
    main.classList.add('visivel');

    // Animação dos elementos entrando
    header.classList.add('animando');

    const boards = document.querySelectorAll('.board-section');
    if (boards[0]) boards[0].classList.add('entrada-esq');
    if (boards[1]) boards[1].classList.add('entrada-ctr');
    if (boards[2]) boards[2].classList.add('entrada-dir');

    requestAnimationFrame(() => {
      setTimeout(() => {
        if (boards[0]) { boards[0].classList.remove('entrada-esq'); }
        if (boards[1]) { boards[1].classList.remove('entrada-ctr'); }
        if (boards[2]) { boards[2].classList.remove('entrada-dir'); }
      }, 50);
    });

    // Inicia música ao fechar intro
    if (!musicaIniciada) { musicaIniciada = true; AudioEngine.startMusic(); }

  }, 650);
}

async function iniciarJogo() {
  const res = await api('nova_partida', {});
  STATE.partidaId = res.partida_id;
  renderBoards();
  renderShipSelector();
  document.getElementById('chat-panel').style.display = 'block';
}

async function novaPartida() {
  document.getElementById('modal-fim').classList.add('hidden');
  STATE.meuTabuleiro = Array.from({length:10}, () => Array(10).fill(null));
  STATE.tiros_j = {};
  STATE.tiros_ia = {};
  STATE.barcoConfig.forEach(b => b.restantes = b.qtd);
  STATE.barcosSelecionado = null;
  STATE.horizontal = true;
  STATE.fase = 'setup';
  STATE.meuTurn = true;

  document.getElementById('phase-setup').style.display = 'block';
  document.getElementById('phase-battle').style.display = 'none';
  document.getElementById('ori-label').textContent = 'HORIZONTAL';

  await iniciarJogo();
}

// ─────────────────────────────────────────
// RENDER BOARDS
// ─────────────────────────────────────────
function renderBoards() {
  renderBoard('my-board', false);
  renderBoard('enemy-board', true);
}

function renderBoard(id, isEnemy) {
  const board = document.getElementById(id);
  board.innerHTML = '';

  // corner
  const corner = document.createElement('div');
  corner.className = 'board-header';
  board.appendChild(corner);

  // column headers (A-J)
  LETRAS.forEach(l => {
    const h = document.createElement('div');
    h.className = 'board-header';
    h.textContent = l;
    board.appendChild(h);
  });

  for (let row = 0; row < 10; row++) {
    // row header
    const rh = document.createElement('div');
    rh.className = 'board-header';
    rh.textContent = row + 1;
    board.appendChild(rh);

    for (let col = 0; col < 10; col++) {
      const cell = document.createElement('div');
      cell.className = 'cell';
      cell.dataset.row = row;
      cell.dataset.col = col;

      if (isEnemy) {
        cell.addEventListener('click', () => atirar(row, col));
        cell.addEventListener('mouseenter', () => {}); // extensible
        updateEnemyCell(cell, row, col);
      } else {
        cell.addEventListener('click', () => colocarBarco(row, col));
        cell.addEventListener('mouseenter', () => previewBarco(row, col));
        cell.addEventListener('mouseleave', () => clearPreview());
        updateMyCell(cell, row, col);
      }

      board.appendChild(cell);
    }
  }
}

function updateEnemyCell(cell, row, col) {
  const key = row + '_' + col;
  cell.innerHTML = '';
  cell.className = 'cell';

  if (STATE.tiros_j[key]) {
    const t = STATE.tiros_j[key];
    cell.classList.add('shot');
    if (t === 'acerto' || t === 'afundou') {
      cell.classList.add('hit');
      cell.innerHTML = '<span class="marker">💥</span>';
    } else {
      cell.classList.add('miss');
      cell.innerHTML = '<span class="marker">🌊</span>';
    }
  }
}

// Animação de afundamento: pisca e some célula por célula
function animarAfundamento(posicoes, boardId, callback) {
  const cells = posicoes.map(p =>
    document.querySelector(`#${boardId} .cell[data-row="${p.l}"][data-col="${p.c}"]`)
  ).filter(Boolean);

  // Fase 1: todas piscam juntas
  cells.forEach(c => c.classList.add('sinking'));

  // Fase 2: somem uma a uma com delay
  setTimeout(() => {
    cells.forEach(c => c.classList.remove('sinking'));
    cells.forEach((c, i) => {
      setTimeout(() => {
        c.classList.add('sunk-anim');
        spawnParticles(c, 5, '#ff6600');
        setTimeout(() => {
          c.classList.remove('sunk-anim');
          c.classList.add('hit');
          c.innerHTML = '<span class="marker">💀</span>';
        }, 400);
      }, i * 120);
    });
    // Flash de tela
    const flash = document.getElementById('screen-flash');
    flash.classList.remove('active');
    void flash.offsetWidth;
    flash.classList.add('active');
    // Callback após animação
    if (callback) setTimeout(callback, cells.length * 120 + 500);
  }, 500);
}

function updateMyCell(cell, row, col) {
  const val = STATE.meuTabuleiro[row][col];
  const key = row + '_' + col;
  cell.innerHTML = '';
  cell.className = 'cell';

  if (val) {
    cell.classList.add('ship');
    if (STATE.tiros_ia[key]) {
      cell.classList.add('ship-ia-hit');
      cell.innerHTML = '<span class="marker">💥</span>';
    }
  } else if (STATE.tiros_ia[key]) {
    cell.classList.add('miss');
    cell.innerHTML = '<span class="marker">🌊</span>';
  }
}

// ─────────────────────────────────────────
// SHIP SELECTOR
// ─────────────────────────────────────────
function renderShipSelector() {
  const list = document.getElementById('ship-list');
  list.innerHTML = '';

  STATE.barcoConfig.forEach((cfg, idx) => {
    const item = document.createElement('div');
    item.className = 'ship-item' + (cfg.restantes === 0 ? ' depleted' : '');
    item.id = 'ship-item-' + idx;
    item.onclick = () => {
      if (cfg.restantes > 0) selecionarBarco(idx);
    };

    const preview = document.createElement('div');
    preview.className = 'ship-preview';
    for (let i = 0; i < cfg.tamanho; i++) {
      const sq = document.createElement('div');
      sq.className = 'ship-sq';
      preview.appendChild(sq);
    }

    item.innerHTML = `
      <div class="ship-info">
        <div class="ship-name">${cfg.nome}</div>
        <div class="ship-count">${cfg.tamanho} quadrado${cfg.tamanho>1?'s':''} · Restam: ${cfg.restantes}</div>
      </div>
    `;
    item.prepend(preview);
    list.appendChild(item);
  });

  verificarProntoParaIniciar();
}

function selecionarBarco(idx) {
  STATE.barcosSelecionado = idx;
  document.querySelectorAll('.ship-item').forEach(el => el.classList.remove('selected'));
  const el = document.getElementById('ship-item-' + idx);
  if (el) el.classList.add('selected');
}

function toggleOrientation() {
  STATE.horizontal = !STATE.horizontal;
  document.getElementById('ori-label').textContent = STATE.horizontal ? 'HORIZONTAL' : 'VERTICAL';
}

// ─────────────────────────────────────────
// PLACEMENT
// ─────────────────────────────────────────
function getPosicoes(row, col, tamanho, horizontal) {
  const pos = [];
  for (let i = 0; i < tamanho; i++) {
    pos.push({ l: horizontal ? row : row + i, c: horizontal ? col + i : col });
  }
  return pos;
}

function posicaoValida(posicoes) {
  return posicoes.every(p =>
    p.l >= 0 && p.l < 10 && p.c >= 0 && p.c < 10 && !STATE.meuTabuleiro[p.l][p.c]
  );
}

function previewBarco(row, col) {
  if (STATE.fase !== 'setup' || STATE.barcosSelecionado === null) return;
  clearPreview();
  const cfg = STATE.barcoConfig[STATE.barcosSelecionado];
  if (cfg.restantes === 0) return;

  const posicoes = getPosicoes(row, col, cfg.tamanho, STATE.horizontal);
  const valido = posicaoValida(posicoes);

  posicoes.forEach(p => {
    if (p.l < 0 || p.l >= 10 || p.c < 0 || p.c >= 10) return;
    const cell = getCellMy(p.l, p.c);
    if (cell) cell.classList.add(valido ? 'placing-valid' : 'placing-invalid');
  });
}

function clearPreview() {
  document.querySelectorAll('#my-board .cell').forEach(c => {
    c.classList.remove('placing-valid', 'placing-invalid');
  });
}

function colocarBarco(row, col) {
  if (STATE.fase !== 'setup' || STATE.barcosSelecionado === null) return;
  const cfg = STATE.barcoConfig[STATE.barcosSelecionado];
  if (cfg.restantes === 0) return;

  const posicoes = getPosicoes(row, col, cfg.tamanho, STATE.horizontal);
  if (!posicaoValida(posicoes)) return;

  posicoes.forEach(p => { STATE.meuTabuleiro[p.l][p.c] = cfg.tipo; });
  cfg.restantes--;

  // Re-render cells
  posicoes.forEach(p => {
    const cell = getCellMy(p.l, p.c);
    if (cell) updateMyCell(cell, p.l, p.c);
  });

  // Reset selection if depleted
  if (cfg.restantes === 0) STATE.barcosSelecionado = null;

  renderShipSelector();
  if (STATE.barcosSelecionado !== null) selecionarBarco(STATE.barcosSelecionado);
}

function limparBarcos() {
  STATE.meuTabuleiro = Array.from({length:10}, () => Array(10).fill(null));
  STATE.barcoConfig.forEach(b => b.restantes = b.qtd);
  STATE.barcosSelecionado = null;
  renderBoards();
  renderShipSelector();
}

function verificarProntoParaIniciar() {
  const prontos = STATE.barcoConfig.every(b => b.restantes === 0);
  document.getElementById('btn-iniciar').disabled = !prontos;
}

// ─────────────────────────────────────────
// INICIAR BATALHA
// ─────────────────────────────────────────
async function iniciarBatalha() {
  const barcos = [];
  STATE.barcoConfig.forEach(cfg => {
    const tipoPosicoes = [];
    for (let r = 0; r < 10; r++) {
      for (let c = 0; c < 10; c++) {
        if (STATE.meuTabuleiro[r][c] === cfg.tipo) {
          tipoPosicoes.push({l:r, c});
        }
      }
    }
    // Agrupa em barcos individuais
    const usados = Array.from({length:10}, () => Array(10).fill(false));
    tipoPosicoes.forEach(pos => {
      if (usados[pos.l][pos.c]) return;
      const barcoPos = [];
      // verifica horizontal
      let h = true, v = true;
      for (let i = 0; i < cfg.tamanho; i++) {
        if (pos.c + i >= 10 || STATE.meuTabuleiro[pos.l][pos.c+i] !== cfg.tipo) h = false;
        if (pos.l + i >= 10 || STATE.meuTabuleiro[pos.l+i]?.[pos.c] !== cfg.tipo) v = false;
      }
      if (h) {
        for (let i = 0; i < cfg.tamanho; i++) { barcoPos.push({l:pos.l, c:pos.c+i}); usados[pos.l][pos.c+i]=true; }
      } else if (v) {
        for (let i = 0; i < cfg.tamanho; i++) { barcoPos.push({l:pos.l+i, c:pos.c}); usados[pos.l+i][pos.c]=true; }
      } else {
        barcoPos.push(pos); usados[pos.l][pos.c]=true;
      }
      if (barcoPos.length === cfg.tamanho) {
        barcos.push({tipo:cfg.tipo, tamanho:cfg.tamanho, posicoes:barcoPos});
      }
    });
  });

  const res = await api('posicionar_barcos', {barcos: JSON.stringify(barcos)});
  if (res.erro) { alert(res.erro); return; }

  STATE.fase = 'battle';
  document.getElementById('phase-setup').style.display = 'none';
  document.getElementById('phase-battle').style.display = 'block';
  document.getElementById('hdr-status').textContent = '⚔ EM BATALHA';
  document.body.classList.add('battle-active');

  addLog('⚔ Batalha iniciada! Ataque em Y1 a J10.', 'vitoria');
  adicionarMsgChat('resposta', 'A batalha começa AGORA! Que os mares tragam sua derrota, recruta!');
}

// ─────────────────────────────────────────
// ATIRAR
// ─────────────────────────────────────────
let esperandoIA = false;

async function atirar(row, col) {
  if (STATE.fase !== 'battle' || !STATE.meuTurn || esperandoIA) return;
  const key = row + '_' + col;
  if (STATE.tiros_j[key]) return;

  esperandoIA = true;
  STATE.meuTurn = false;
  setTurnIndicator('ia');

  const res = await api('atirar', {linha: row, coluna: col});
  if (res.erro) { alert(res.erro); STATE.meuTurn = true; esperandoIA = false; return; }

  // Processa tiro do jogador
  const tj = res.jogador;
  STATE.tiros_j[key] = tj.resultado;
  const cellE = getCellEnemy(row, col);
  if (cellE) updateEnemyCell(cellE, row, col);

  // 🔊 SOM do tiro do jogador
  if      (tj.resultado === 'agua')    { AudioEngine.sfxAgua();    zeusReagir('agua_jogador'); }
  else if (tj.resultado === 'afundou') { AudioEngine.sfxAfundou(); zeusReagir('afundou_jogador'); }
  else                                 { AudioEngine.sfxAcerto();  zeusReagir('acerto_jogador'); }

  // ✨ EFEITOS VISUAIS do tiro do jogador
  const cellEfx = getCellEnemy(row, col);
  if (cellEfx) {
    if (tj.resultado === 'acerto' || tj.resultado === 'afundou') {
      cellEfx.classList.add('shockwave');
      setTimeout(() => cellEfx.classList.remove('shockwave'), 600);
      spawnParticles(cellEfx, tj.resultado === 'afundou' ? 12 : 6, '#ff4400');
    } else {
      spawnParticles(cellEfx, 4, '#0099bb');
    }
  }
  showFloatText(row, col, tj.resultado, false);

  const pos = LETRAS[col] + (row+1);
  const emojiJ = tj.resultado === 'agua' ? '🌊' : tj.resultado === 'afundou' ? '💀' : '💥';
  addLog(`${emojiJ} Você → ${pos}: ${tj.resultado.toUpperCase()}`, 'jogador');

  // Animação de afundamento do barco inimigo
  if (tj.resultado === 'afundou' && res.posicoes_afundadas) {
    animarAfundamento(res.posicoes_afundadas, 'enemy-board', null);
  }

  if (tj.venceu) {
    setTimeout(() => finalizarJogo(true, res), tj.resultado === 'afundou' ? 900 : 0);
    esperandoIA = false;
    return;
  }

  // Processa tiro da IA
  if (res.ia) {
    const tia = res.ia;
    const keyIA = tia.linha + '_' + tia.coluna;
    STATE.tiros_ia[keyIA] = tia.resultado;
    const cellM = getCellMy(tia.linha, tia.coluna);
    if (cellM) updateMyCell(cellM, tia.linha, tia.coluna);

    const emojiIA = tia.resultado === 'agua' ? '🌊' : tia.resultado === 'afundou' ? '💀' : '💥';
    addLog(`${emojiIA} Zeus → ${tia.posicao_texto}: ${tia.resultado.toUpperCase()}`, 'ia');

    // 🔊 SOM + EFEITOS do tiro da IA
    setTimeout(() => {
      if      (tia.resultado === 'agua')    { AudioEngine.sfxAgua();    zeusReagir('agua_ia'); }
      else if (tia.resultado === 'afundou') { AudioEngine.sfxAfundou(); zeusReagir('afundou_ia'); }
      else                                  { AudioEngine.sfxAcerto();  zeusReagir('acerto_ia'); }

      const cellIA = getCellMy(tia.linha, tia.coluna);
      if (cellIA) {
        if (tia.resultado !== 'agua') {
          cellIA.classList.add('shockwave');
          setTimeout(() => cellIA.classList.remove('shockwave'), 600);
          spawnParticles(cellIA, tia.resultado === 'afundou' ? 10 : 5, '#ff2200');
        }
      }
      showFloatText(tia.linha, tia.coluna, tia.resultado, true);

      // Animação de afundamento do barco do jogador
      if (tia.resultado === 'afundou' && tia.posicoes_afundadas) {
        animarAfundamento(tia.posicoes_afundadas, 'my-board', null);
      }
    }, 300);

    if (tia.comentario) {
      adicionarMsgChat('resposta', tia.comentario);
    }

    if (tia.venceu) {
      finalizarJogo(false, res);
      esperandoIA = false;
      return;
    }

    // Atualiza stats
    await atualizarStats();
  }

  STATE.meuTurn = true;
  esperandoIA = false;
  setTurnIndicator('jogador');
}

// ─────────────────────────────────────────
// ESTADO / STATS
// ─────────────────────────────────────────
async function atualizarStats() {
  const res = await api('estado', {});
  if (!res.partida) return;
  const p = res.partida;
  document.getElementById('stat-tiros-j').textContent  = p.tiros_jogador;
  document.getElementById('stat-acertos-j').textContent = p.acertos_jogador;
  document.getElementById('stat-tiros-i').textContent  = p.tiros_ia;
  document.getElementById('stat-acertos-i').textContent = p.acertos_ia;
  document.getElementById('hdr-tiros').innerHTML = `TIROS: <strong>${p.tiros_jogador}</strong>`;
}

// ─────────────────────────────────────────
// FIM DE JOGO
// ─────────────────────────────────────────
function finalizarJogo(jogadorVenceu, res) {
  STATE.fase = 'over';
  AudioEngine.stopMusic();

  const modal = document.getElementById('modal-fim');
  modal.classList.remove('hidden');

  if (jogadorVenceu) {
    AudioEngine.sfxVitoria();
    zeusReagir('vitoria');
    document.getElementById('modal-icon').textContent = '🏆';
    document.getElementById('modal-title').className = 'modal-title win';
    document.getElementById('modal-title').textContent = 'VITÓRIA!';
    document.getElementById('modal-body').textContent = 'Você afundou toda a frota do Almirante ZEUS! Os mares são seus, Almirante!';
    addLog('🏆 VOCÊ VENCEU! Frota inimiga destruída!', 'vitoria');
    setTimeout(lancarConfetes, 400);
    // Texto épico
    setTimeout(() => {
      const el = document.createElement('div');
      el.className = 'float-text win';
      el.textContent = '🏆 VITÓRIA!';
      el.style.cssText = 'left:50%;top:40%;transform:translateX(-50%);font-size:3rem;';
      document.body.appendChild(el);
      setTimeout(() => el.remove(), 1200);
    }, 200);
  } else {
    AudioEngine.sfxDerrota();
    zeusReagir('derrota');
    document.getElementById('modal-icon').textContent = '💀';
    document.getElementById('modal-title').className = 'modal-title lose';
    document.getElementById('modal-title').textContent = 'DERROTA!';
    document.getElementById('modal-body').textContent = 'O Almirante ZEUS afundou toda a sua frota. Tente novamente, recruta!';
    addLog('💀 ZEUS VENCEU! Sua frota foi destruída!', 'afundou');
  }
}

// ─────────────────────────────────────────
// CHAT
// ─────────────────────────────────────────
async function enviarChat() {
  const input = document.getElementById('chat-input');
  const msg = input.value.trim();
  if (!msg) return;
  input.value = '';

  adicionarMsgChat('pergunta', msg);

  zeusReagir('chat');
  const loadEl = adicionarMsgChat('resposta', '<span class="loading-dots">Almirante pensa</span>', true);

  const res = await api('chat', {mensagem: msg});
  loadEl.remove();
  adicionarMsgChat('resposta', res.resposta || 'Nenhuma resposta...');
}

function adicionarMsgChat(tipo, msg, isTemp = false) {
  const chat = document.getElementById('chat-box');
  const div = document.createElement('div');
  div.className = 'chat-msg ' + tipo;
  div.innerHTML = `<div class="who">${tipo === 'pergunta' ? '🎖 VOCÊ' : '⚡ ALMIRANTE ZEUS'}</div>${msg}`;
  chat.appendChild(div);
  chat.scrollTop = chat.scrollHeight;
  return div;
}

// ─────────────────────────────────────────
// LOG
// ─────────────────────────────────────────
function addLog(msg, tipo = '') {
  const log = document.getElementById('log-box');
  const div = document.createElement('div');
  div.className = 'log-entry ' + tipo;
  div.textContent = msg;
  log.appendChild(div);
  log.scrollTop = log.scrollHeight;
}

// ─────────────────────────────────────────
// UI HELPERS
// ─────────────────────────────────────────
function setTurnIndicator(quem) {
  const el = document.getElementById('turn-indicator');
  if (quem === 'jogador') {
    el.className = 'turn-indicator player-turn';
    el.innerHTML = '🎯 SUA VEZ DE ATACAR';
  } else {
    el.className = 'turn-indicator ia-turn';
    el.innerHTML = '<span class="radar-icon">📡</span> ZEUS CALCULANDO...';
  }
}

// ── Partículas de explosão ───────────────────────────────────────
function spawnParticles(cell, count, color) {
  const rect = cell.getBoundingClientRect();
  const cx = rect.left + rect.width  / 2;
  const cy = rect.top  + rect.height / 2;
  for (let i = 0; i < count; i++) {
    const p = document.createElement('div');
    p.className = 'splash-particle';
    const angle  = (Math.PI * 2 * i) / count + Math.random() * 0.5;
    const dist   = 20 + Math.random() * 35;
    p.style.cssText = `
      left: ${cx}px; top: ${cy}px;
      background: ${color};
      --sx: ${Math.cos(angle) * dist}px;
      --sy: ${Math.sin(angle) * dist}px;
      animation-delay: ${Math.random() * 0.1}s;
      animation-duration: ${0.4 + Math.random() * 0.3}s;
    `;
    document.body.appendChild(p);
    setTimeout(() => p.remove(), 900);
  }
}

// ── Texto flutuante de resultado ─────────────────────────────────
function showFloatText(row, col, resultado, isMine) {
  const boardId = isMine ? 'my-board' : 'enemy-board';
  const cell = document.querySelector(`#${boardId} .cell[data-row="${row}"][data-col="${col}"]`);
  if (!cell) return;
  const rect = cell.getBoundingClientRect();
  const el = document.createElement('div');
  el.className = 'float-text';
  const textos = { agua: '🌊 ÁGUA!', acerto: '💥 ACERTO!', afundou: '💀 AFUNDOU!' };
  const classes = { agua: 'miss', acerto: 'hit', afundou: 'sunk' };
  el.textContent = textos[resultado] || '';
  el.classList.add(classes[resultado] || 'hit');
  el.style.cssText = `left:${rect.left + rect.width/2 - 50}px; top:${rect.top}px; width:100px; text-align:center;`;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 1100);
}

// ── Confetes de vitória ───────────────────────────────────────────
function lancarConfetes() {
  const modal = document.querySelector('.modal');
  if (!modal) return;
  const cores = ['#00ff88','#00d4ff','#ffaa00','#ff6600','#ff3399'];
  for (let i = 0; i < 30; i++) {
    const c = document.createElement('div');
    c.className = 'confetti-piece';
    c.style.cssText = `
      left: ${Math.random() * 100}%;
      top: -10px;
      background: ${cores[Math.floor(Math.random() * cores.length)]};
      animation-delay: ${Math.random() * 0.8}s;
      animation-duration: ${1 + Math.random() * 0.5}s;
      transform: rotate(${Math.random() * 360}deg);
    `;
    modal.appendChild(c);
    setTimeout(() => c.remove(), 2000);
  }
}

// ─────────────────────────────────────────
// AVATAR DO ALMIRANTE ZEUS — Pixel Art Canvas
// ─────────────────────────────────────────
const ZEUS_FACES = {
  neutro: {
    cor_rosto: '#c8a46e', cor_barba: '#e8e8e8', cor_chapeu: '#1a2a4a',
    olho_l: [2,1], olho_r: [4,1],   // posições dos olhos
    boca: 'reta',   sobrancelha: 'normal',
  },
  alegria: {
    cor_rosto: '#d4b07a', cor_barba: '#e8e8e8', cor_chapeu: '#1a2a4a',
    olho_l: [2,1], olho_r: [4,1],
    boca: 'sorriso', sobrancelha: 'levantada',
  },
  raiva: {
    cor_rosto: '#c84a2a', cor_barba: '#e8e8e8', cor_chapeu: '#1a2a4a',
    olho_l: [2,1], olho_r: [4,1],
    boca: 'raiva',  sobrancelha: 'franzida',
  },
  espanto: {
    cor_rosto: '#c8a46e', cor_barba: '#e8e8e8', cor_chapeu: '#1a2a4a',
    olho_l: [2,1], olho_r: [4,1],
    boca: 'aberta', sobrancelha: 'espanto',
  },
  desespero: {
    cor_rosto: '#9a7040', cor_barba: '#cccccc', cor_chapeu: '#1a2a4a',
    olho_l: [2,1], olho_r: [4,1],
    boca: 'triste', sobrancelha: 'triste',
  },
};

function zeusAvatar(expressao = 'neutro') {
  const canvas = document.getElementById('zeus-avatar');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const f   = ZEUS_FACES[expressao] || ZEUS_FACES.neutro;
  const S   = 52; // tamanho do canvas
  const P   = 4;  // pixels por "bloco"

  ctx.clearRect(0, 0, S, S);

  // Fundo
  ctx.fillStyle = '#0d2040';
  ctx.fillRect(0, 0, S, S);

  // Chapéu de almirante
  ctx.fillStyle = f.cor_chapeu;
  ctx.fillRect(4, 2, 44, 10);   // aba
  ctx.fillRect(10, 0, 32, 6);   // copa
  // Dourado do chapéu
  ctx.fillStyle = '#ffaa00';
  ctx.fillRect(10, 10, 32, 2);

  // Rosto
  ctx.fillStyle = f.cor_rosto;
  ctx.fillRect(10, 14, 32, 20);

  // Olhos
  ctx.fillStyle = expressao === 'raiva' ? '#cc2200' : '#1a1a2e';
  ctx.fillRect(14, 18, 6, 6);  // olho esq
  ctx.fillRect(32, 18, 6, 6);  // olho dir
  // Brilho dos olhos
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(16, 19, 2, 2);
  ctx.fillRect(34, 19, 2, 2);

  // Sobrancelhas
  ctx.fillStyle = '#553300';
  if (f.sobrancelha === 'franzida') {
    ctx.fillRect(14, 15, 8, 2);
    ctx.fillRect(30, 15, 8, 2);
    ctx.fillRect(20, 14, 2, 2);  // V no meio
    ctx.fillRect(28, 14, 2, 2);
  } else if (f.sobrancelha === 'levantada') {
    ctx.fillRect(14, 13, 8, 2);
    ctx.fillRect(30, 13, 8, 2);
  } else if (f.sobrancelha === 'espanto') {
    ctx.fillRect(12, 12, 10, 2);
    ctx.fillRect(30, 12, 10, 2);
  } else if (f.sobrancelha === 'triste') {
    ctx.fillRect(14, 16, 8, 2);
    ctx.fillRect(30, 16, 8, 2);
    ctx.fillRect(14, 15, 2, 2);
    ctx.fillRect(38, 15, 2, 2);
  } else {
    ctx.fillRect(14, 15, 8, 2);
    ctx.fillRect(30, 15, 8, 2);
  }

  // Nariz
  ctx.fillStyle = '#aa7744';
  ctx.fillRect(24, 22, 4, 4);

  // Boca
  ctx.fillStyle = '#220000';
  if (f.boca === 'sorriso') {
    ctx.fillRect(14, 30, 4, 2);
    ctx.fillRect(18, 32, 16, 2);
    ctx.fillRect(34, 30, 4, 2);
    ctx.fillStyle = '#ff8866';
    ctx.fillRect(18, 30, 16, 2);
  } else if (f.boca === 'raiva') {
    ctx.fillRect(14, 32, 24, 2);
    ctx.fillRect(14, 30, 4, 2);
    ctx.fillRect(34, 30, 4, 2);
  } else if (f.boca === 'aberta') {
    ctx.fillRect(16, 29, 20, 5);
    ctx.fillStyle = '#cc4422';
    ctx.fillRect(18, 30, 16, 4);
    ctx.fillStyle = '#ffaaaa';
    ctx.fillRect(20, 31, 12, 2);
  } else if (f.boca === 'triste') {
    ctx.fillRect(16, 33, 4, 2);
    ctx.fillRect(20, 31, 12, 2);
    ctx.fillRect(32, 33, 4, 2);
  } else {
    ctx.fillRect(16, 31, 20, 2);
  }

  // Barba / bigode
  ctx.fillStyle = f.cor_barba;
  ctx.fillRect(8,  34, 36, 6);   // barba
  ctx.fillRect(12, 26, 6, 6);    // bigode esq
  ctx.fillRect(34, 26, 6, 6);    // bigode dir
  ctx.fillRect(22, 27, 8, 3);    // separação bigode

  // Ombros / uniforme
  ctx.fillStyle = '#1a2a6a';
  ctx.fillRect(2, 40, 48, 12);
  // Dragonas douradas
  ctx.fillStyle = '#ffaa00';
  ctx.fillRect(2,  40, 10, 4);
  ctx.fillRect(40, 40, 10, 4);
  // Medalhas
  ctx.fillStyle = '#ffdd00';
  ctx.fillRect(14, 43, 4, 4);
  ctx.fillStyle = '#ff4400';
  ctx.fillRect(20, 43, 4, 4);
  ctx.fillStyle = '#00aaff';
  ctx.fillRect(26, 43, 4, 4);

  // Borda do canvas
  ctx.strokeStyle = expressao === 'raiva'   ? '#ff3333' :
                    expressao === 'alegria' ? '#00ff88' :
                    expressao === 'espanto' ? '#00d4ff' : '#ffaa00';
  ctx.lineWidth = 2;
  ctx.strokeRect(1, 1, S-2, S-2);

  // Atualiza classe CSS
  const el = canvas.parentElement?.querySelector ? canvas : document.getElementById('zeus-avatar');
  el.className = `zeus-avatar ${expressao}`;
  el.classList.add('mudando');
  setTimeout(() => el.classList.remove('mudando'), 350);
}

// Muda expressão do Zeus baseado no evento
function zeusReagir(evento) {
  const mapa = {
    'acerto_jogador': 'raiva',      // jogador acertou meu barco
    'afundou_jogador': 'desespero', // jogador afundou meu barco
    'acerto_ia': 'alegria',         // eu acertei o jogador
    'afundou_ia': 'alegria',        // eu afundei o jogador
    'agua_ia': 'neutro',            // eu errei
    'agua_jogador': 'alegria',      // jogador errou
    'vitoria': 'desespero',         // jogador venceu
    'derrota': 'alegria',           // eu venci
    'chat': 'espanto',              // mensagem no chat
  };
  zeusAvatar(mapa[evento] || 'neutro');
}

function toggleMusica() {
  const btn = document.getElementById('btn-musica');
  const on = AudioEngine.toggleMusic();
  btn.textContent = on ? '🎵 MÚSICA' : '🔇 MUDO';
  btn.classList.toggle('on', on);
}

function getCellMy(row, col) {
  return document.querySelector(`#my-board .cell[data-row="${row}"][data-col="${col}"]`);
}
function getCellEnemy(row, col) {
  return document.querySelector(`#enemy-board .cell[data-row="${row}"][data-col="${col}"]`);
}

// ─────────────────────────────────────────
// API
// ─────────────────────────────────────────
async function api(action, data = {}) {
  try {
    const body = new URLSearchParams({action, ...data});
    const res = await fetch('api.php', {method:'POST', body});
    const json = await res.json();
    // Mostra erros de debug da IA no console
    if (json._debug_erro) console.warn('[GEMINI DEBUG]', json._debug_erro);
    return json;
  } catch (e) {
    console.error('API error:', e);
    return {erro: 'Falha de comunicação com o servidor.'};
  }
}

// ═══════════════════════════════════════════════════════
// 🎵 MOTOR DE SOM & MÚSICA (Web Audio API — sem arquivos)
// ═══════════════════════════════════════════════════════
const AudioEngine = (() => {
  let ctx = null;
  let musicPlaying = false;
  let musicNodes = [];
  let masterGain = null;
  let musicGain = null;

  function getCtx() {
    if (!ctx) {
      ctx = new (window.AudioContext || window.webkitAudioContext)();
      masterGain = ctx.createGain();
      masterGain.gain.value = 1.0;
      masterGain.connect(ctx.destination);
      musicGain = ctx.createGain();
      musicGain.gain.value = 0.18; // volume da música de fundo
      musicGain.connect(masterGain);
    }
    if (ctx.state === 'suspended') ctx.resume();
    return ctx;
  }

  // ── Gerador de nota sintética ──
  function playTone(freq, duration, type = 'sine', vol = 0.4, delay = 0, dest = null) {
    const c = getCtx();
    const osc = c.createOscillator();
    const g   = c.createGain();
    osc.type      = type;
    osc.frequency.setValueAtTime(freq, c.currentTime + delay);
    g.gain.setValueAtTime(0, c.currentTime + delay);
    g.gain.linearRampToValueAtTime(vol, c.currentTime + delay + 0.01);
    g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + delay + duration);
    osc.connect(g);
    g.connect(dest || masterGain);
    osc.start(c.currentTime + delay);
    osc.stop(c.currentTime + delay + duration + 0.05);
    return { osc, g };
  }

  // ── Ruído de água / splash ──
  function playNoise(duration, vol = 0.15, delay = 0) {
    const c = getCtx();
    const bufSize = c.sampleRate * duration;
    const buf     = c.createBuffer(1, bufSize, c.sampleRate);
    const data    = buf.getChannelData(0);
    for (let i = 0; i < bufSize; i++) data[i] = Math.random() * 2 - 1;

    const src    = c.createBufferSource();
    const filter = c.createBiquadFilter();
    const g      = c.createGain();
    src.buffer    = buf;
    filter.type   = 'bandpass';
    filter.frequency.value = 400;
    filter.Q.value = 0.5;
    g.gain.setValueAtTime(0, c.currentTime + delay);
    g.gain.linearRampToValueAtTime(vol, c.currentTime + delay + 0.02);
    g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + delay + duration);
    src.connect(filter);
    filter.connect(g);
    g.connect(masterGain);
    src.start(c.currentTime + delay);
    src.stop(c.currentTime + delay + duration);
  }

  // ════════════════════════════════
  // SONS DE EFEITO
  // ════════════════════════════════

  // 💧 ÁGUA — tiro que errou
  function sfxAgua() {
    playNoise(0.6, 0.25);
    playTone(180, 0.4, 'sine', 0.15, 0.05);
    playTone(120, 0.3, 'sine', 0.10, 0.2);
  }

  // 💥 ACERTO — tiro que acertou
  function sfxAcerto() {
    // Boom grave
    playTone(80,  0.5, 'sawtooth', 0.5);
    playTone(160, 0.3, 'square',   0.3, 0.05);
    // Estalido agudo
    playTone(800, 0.08, 'square', 0.2, 0.0);
    playTone(400, 0.12, 'square', 0.15, 0.08);
    playNoise(0.3, 0.35, 0.0);
  }

  // 💀 AFUNDOU — barco afundado
  function sfxAfundou() {
    // Explosão
    sfxAcerto();
    // Descida dramática
    const c = getCtx();
    const osc = c.createOscillator();
    const g   = c.createGain();
    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(300, c.currentTime + 0.1);
    osc.frequency.exponentialRampToValueAtTime(40, c.currentTime + 1.2);
    g.gain.setValueAtTime(0.35, c.currentTime + 0.1);
    g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + 1.3);
    osc.connect(g); g.connect(masterGain);
    osc.start(c.currentTime + 0.1);
    osc.stop(c.currentTime + 1.4);
    // Borbulhas
    for (let i = 0; i < 4; i++) playNoise(0.2, 0.1, 0.3 + i * 0.2);
  }

  // 🏆 VITÓRIA
  function sfxVitoria() {
    // Fanfarra de pirata: Sol-Sol-Sol-Mi-Lá
    const notas = [392,392,392,330,440,523,392,330,440,523];
    const durs  = [0.15,0.15,0.15,0.3,0.15,0.5,0.15,0.15,0.15,0.6];
    let t = 0;
    notas.forEach((f, i) => {
      playTone(f, durs[i] * 0.9, 'square', 0.35, t);
      playTone(f * 1.5, durs[i] * 0.9, 'sine', 0.12, t);
      t += durs[i];
    });
    // Ruído de confete
    setTimeout(() => playNoise(0.8, 0.1), 200);
  }

  // 💀 DERROTA
  function sfxDerrota() {
    // Descida triste: Lá-Sol-Fá-Mi
    const notas = [440, 392, 349, 330, 294, 262];
    const durs  = [0.3, 0.3, 0.4, 0.3, 0.3, 0.8];
    let t = 0;
    notas.forEach((f, i) => {
      playTone(f, durs[i] * 0.85, 'sine', 0.3, t);
      t += durs[i];
    });
    // Boom final
    setTimeout(() => {
      playTone(60, 1.0, 'sawtooth', 0.4);
      playNoise(0.8, 0.3);
    }, 1000);
  }

  // 🎵 MÚSICA DE PIRATA — Shanty com melodia, harmonia, baixo e percussão
  function startMusic() {
    if (musicPlaying) return;
    musicPlaying = true;
    getCtx();

    // Melodia principal — Shanty estilo pirata
    const melody = [
      [392,0.3,'triangle',0.5],[392,0.15,'triangle',0.4],[440,0.15,'triangle',0.5],
      [392,0.3,'triangle',0.5],[349,0.3,'triangle',0.45],[392,0.55,'triangle',0.5],
      [392,0.3,'triangle',0.5],[392,0.15,'triangle',0.4],[440,0.15,'triangle',0.5],
      [392,0.3,'triangle',0.5],[440,0.3,'triangle',0.45],[523,0.55,'triangle',0.5],
      [523,0.3,'triangle',0.5],[587,0.15,'triangle',0.4],[523,0.15,'triangle',0.45],
      [494,0.3,'triangle',0.5],[440,0.3,'triangle',0.45],[392,0.55,'triangle',0.5],
      [349,0.3,'triangle',0.5],[392,0.15,'triangle',0.4],[440,0.15,'triangle',0.45],
      [349,0.3,'triangle',0.5],[294,0.3,'triangle',0.4],[330,0.55,'triangle',0.45],
      [392,0.3,'triangle',0.5],[392,0.15,'triangle',0.4],[440,0.15,'triangle',0.5],
      [392,0.3,'triangle',0.5],[523,0.3,'triangle',0.45],[587,0.55,'triangle',0.5],
      [659,0.3,'triangle',0.5],[587,0.15,'triangle',0.4],[523,0.15,'triangle',0.45],
      [494,0.3,'triangle',0.5],[440,0.3,'triangle',0.45],[392,0.55,'triangle',0.5],
      [349,0.3,'triangle',0.45],[392,0.15,'triangle',0.4],[440,0.15,'triangle',0.45],
      [392,0.3,'triangle',0.5],[349,0.3,'triangle',0.45],[294,0.85,'triangle',0.5],
    ];

    // Harmonia (uma terça abaixo da melodia)
    const harmony = melody.map(([f, d, tp, v]) => [f * 0.794, d, 'sine', v * 0.3]);

    // Baixo — padrão de 2 em 2
    const bass = [
      [98,0.55],[98,0.55],[110,0.55],[110,0.55],
      [98,0.55],[98,0.55],[87,0.55],[87,0.55],
      [98,0.55],[98,0.55],[110,0.55],[110,0.55],
      [130,0.55],[130,0.55],[110,0.55],[87,1.1],
    ];

    // Percussão — bumbo e caixa simulados com ruído filtrado
    const perc = [
      ['kick',0.3],['hi',0.15],['snare',0.15],['hi',0.15],
      ['kick',0.15],['hi',0.15],['snare',0.15],['hi',0.15],
      ['kick',0.3],['hi',0.15],['snare',0.15],['hi',0.15],
      ['kick',0.15],['kick',0.15],['snare',0.15],['hi',0.45],
    ];

    function playPercNote(type, delay) {
      const c = getCtx();
      const bufSize = c.sampleRate * 0.15;
      const buf  = c.createBuffer(1, bufSize, c.sampleRate);
      const data = buf.getChannelData(0);
      for (let i = 0; i < bufSize; i++) data[i] = Math.random() * 2 - 1;
      const src = c.createBufferSource();
      const flt = c.createBiquadFilter();
      const g   = c.createGain();
      src.buffer = buf;
      if (type === 'kick')  { flt.type='lowpass';  flt.frequency.value=120; g.gain.setValueAtTime(0.35, c.currentTime+delay); }
      if (type === 'snare') { flt.type='bandpass'; flt.frequency.value=300; flt.Q.value=0.8; g.gain.setValueAtTime(0.2, c.currentTime+delay); }
      if (type === 'hi')    { flt.type='highpass'; flt.frequency.value=6000; g.gain.setValueAtTime(0.08, c.currentTime+delay); }
      g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + delay + 0.12);
      src.connect(flt); flt.connect(g); g.connect(musicGain);
      src.start(c.currentTime + delay);
      musicNodes.push(src);
    }

    const totalDur = melody.reduce((a,[,d]) => a + d, 0);

    function scheduleLoop(offset) {
      if (!musicPlaying) return;
      const c   = getCtx();
      const now = c.currentTime + offset;

      // Melodia principal
      let t = 0;
      melody.forEach(([freq, dur, type, vol]) => {
        const osc = c.createOscillator();
        const g   = c.createGain();
        osc.type = type;
        osc.frequency.value = freq;
        g.gain.setValueAtTime(0, now+t);
        g.gain.linearRampToValueAtTime(vol, now+t+0.015);
        g.gain.exponentialRampToValueAtTime(0.001, now+t+dur*0.88);
        osc.connect(g); g.connect(musicGain);
        osc.start(now+t); osc.stop(now+t+dur);
        musicNodes.push(osc);
        t += dur;
      });

      // Harmonia
      t = 0;
      harmony.forEach(([freq, dur, type, vol]) => {
        const osc = c.createOscillator();
        const g   = c.createGain();
        osc.type = type;
        osc.frequency.value = freq;
        g.gain.setValueAtTime(0, now+t);
        g.gain.linearRampToValueAtTime(vol, now+t+0.02);
        g.gain.exponentialRampToValueAtTime(0.001, now+t+dur*0.85);
        osc.connect(g); g.connect(musicGain);
        osc.start(now+t); osc.stop(now+t+dur);
        musicNodes.push(osc);
        t += dur;
      });

      // Baixo
      let tb = 0;
      const bassDur = bass.reduce((a,[,d])=>a+d,0);
      const bassReps = Math.ceil(totalDur / bassDur);
      for (let r = 0; r < bassReps; r++) {
        bass.forEach(([freq, dur]) => {
          if (tb >= totalDur) return;
          const osc = c.createOscillator();
          const g   = c.createGain();
          osc.type = 'sine';
          osc.frequency.value = freq;
          g.gain.setValueAtTime(0, now+tb);
          g.gain.linearRampToValueAtTime(0.45, now+tb+0.04);
          g.gain.exponentialRampToValueAtTime(0.001, now+tb+dur*0.82);
          osc.connect(g); g.connect(musicGain);
          osc.start(now+tb); osc.stop(now+tb+dur);
          musicNodes.push(osc);
          tb += dur;
        });
      }

      // Percussão
      let tp = 0;
      const percDur = perc.reduce((a,[,d])=>a+d,0);
      const percReps = Math.ceil(totalDur / percDur);
      for (let r = 0; r < percReps; r++) {
        perc.forEach(([type, dur]) => {
          if (tp >= totalDur) return;
          playPercNote(type, tp);
          tp += dur;
        });
      }

      setTimeout(() => scheduleLoop(0), (totalDur - 0.3) * 1000);
    }

    scheduleLoop(0);
  }

  function stopMusic() {
    musicPlaying = false;
    musicNodes.forEach(n => { try { n.stop(); } catch(e){} });
    musicNodes = [];
  }

  function toggleMusic() {
    if (musicPlaying) { stopMusic(); return false; }
    else { startMusic(); return true; }
  }

  function setMusicVol(v) {
    if (musicGain) musicGain.gain.value = v;
  }

  return { sfxAgua, sfxAcerto, sfxAfundou, sfxVitoria, sfxDerrota, startMusic, stopMusic, toggleMusic };
})();

// Música inicia ao fechar o intro (fecharIntro chama AudioEngine.startMusic)
let musicaIniciada = false;
</script>

<style>
/* Botão de som no header */
.sound-btn {
  background: transparent;
  border: 1px solid var(--navy-light);
  border-radius: 4px;
  color: var(--gray);
  padding: 4px 10px;
  cursor: pointer;
  font-family: 'Share Tech Mono', monospace;
  font-size: 0.75rem;
  transition: all 0.2s;
}
.sound-btn:hover { border-color: var(--cyan); color: var(--cyan); }
.sound-btn.on { border-color: var(--amber); color: var(--amber); }
</style>
</body>
</html>