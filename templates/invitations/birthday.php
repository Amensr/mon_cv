<div class="birthday-invitation" 
     style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color))">
    <div class="confetti"></div>
    <div class="invitation-content">
        <h1 data-field="event_name">Anniversaire</h1>
        <div class="details">
            <div class="detail-item">
                <i class="icon">📅</i>
                <span data-field="date">00/00/0000</span>
            </div>
            <div class="detail-item">
                <i class="icon">⏰</i>
                <span data-field="time">00:00</span>
            </div>
            <div class="detail-item">
                <i class="icon">📍</i>
                <span data-field="location">Lieu</span>
            </div>
        </div>
        <p class="message" data-field="custom_message">
            Venez célébrer avec nous !
        </p>
        <div class="rsvp" data-field="rsvp">
            RSVP: contact@example.com
        </div>
    </div>
</div>

<style>
.birthday-invitation {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    padding: 2rem;
    color: white;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transform-style: preserve-3d;
}

.confetti {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="white" opacity="0.7"/></svg>');
    background-size: 20px 20px;
    animation: confetti-fall 10s linear infinite;
}

@keyframes confetti-fall {
    0% { transform: translateY(-100%) rotate(0deg); }
    100% { transform: translateY(100vh) rotate(360deg); }
}

.invitation-content {
    position: relative;
    z-index: 2;
}

.details {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin: 1.5rem 0;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
}

.message {
    font-size: 1.2rem;
    margin: 2rem 0;
    line-height: 1.6;
}

.rsvp {
    margin-top: 2rem;
    padding: 0.8rem 1.5rem;
    background: white;
    color: var(--primary-color);
    display: inline-block;
    border-radius: 50px;
    font-weight: bold;
}
</style>