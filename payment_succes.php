<?php
// Page de succès de paiement
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement Réussi !</title>
    <style>
        body {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: #222;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .success-box {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.2);
            padding: 40px 60px;
            text-align: center;
            animation: pop 0.7s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes pop {
            0% { transform: scale(0.7); opacity: 0; }
            80% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }
        .success-icon {
            font-size: 64px;
            color: #22c55e;
            margin-bottom: 20px;
            animation: spin 1.2s linear;
        }
        @keyframes spin {
            0% { transform: rotate(-360deg);}
            100% { transform: rotate(0);}
        }
        .confetti {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none;
            z-index: 999;
        }
        .btn-home {
            margin-top: 30px;
            padding: 12px 32px;
            background: #22c55e;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-home:hover {
            background: #16a34a;
        }
    </style>
</head>
<body>
    <canvas class="confetti"></canvas>
    <div class="success-box">
        <div class="success-icon">🎉</div>
        <h1>Paiement réussi !</h1>
        <p>Merci pour votre achat.<br>
        Votre transaction a été traitée avec succès.</p>
        <a href="index.php"><button class="btn-home">Retour à l'accueil</button></a>
    </div>
    <script>
    // Confetti animation
    const canvas = document.querySelector('.confetti');
    const ctx = canvas.getContext('2d');
    let W = window.innerWidth, H = window.innerHeight;
    canvas.width = W; canvas.height = H;
    let confetti = [];
    for(let i=0;i<150;i++){
        confetti.push({
            x: Math.random()*W,
            y: Math.random()*H - H,
            r: Math.random()*6+4,
            d: Math.random()*Math.PI*2,
            color: `hsl(${Math.random()*360},90%,60%)`,
            tilt: Math.random()*10-5,
            tiltAngle: 0
        });
    }
    function draw(){
        ctx.clearRect(0,0,W,H);
        confetti.forEach(c=>{
            ctx.beginPath();
            ctx.ellipse(c.x, c.y, c.r, c.r/2, c.tilt, 0, 2*Math.PI);
            ctx.fillStyle = c.color;
            ctx.fill();
        });
        update();
    }
    function update(){
        confetti.forEach(c=>{
            c.y += Math.cos(c.d)+2+c.r/5;
            c.x += Math.sin(c.d)*2;
            c.tiltAngle += 0.05;
            c.tilt = Math.sin(c.tiltAngle)*10;
            if(c.y > H){
                c.x = Math.random()*W;
                c.y = -10;
            }
        });
    }
    setInterval(draw, 16);
    window.addEventListener('resize', ()=>{
        W = window.innerWidth; H = window.innerHeight;
        canvas.width = W; canvas.height = H;
    });
    </script>
</body>
</html>