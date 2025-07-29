<div class="business-invitation">
    <div class="invitation-logo">
        <img src="assets/images/logo-placeholder.png" alt="Company Logo">
    </div>
    
    <div class="invitation-content">
        <h1>Invitation Professionnelle</h1>
        <h2><?= htmlspecialchars($content['event_name']) ?></h2>
        
        <div class="event-details">
            <p><strong>Date :</strong> <?= htmlspecialchars($content['date']) ?></p>
            <p><strong>Heure :</strong> <?= htmlspecialchars($content['time']) ?></p>
            <p><strong>Lieu :</strong> <?= htmlspecialchars($content['location']) ?></p>
            <p><strong>Dress code :</strong> <?= htmlspecialchars($content['dress_code']) ?></p>
        </div>
        
        <div class="invitation-message">
            <?= nl2br(htmlspecialchars($content['message'])) ?>
        </div>
        
        <div class="rsvp">
            <p>Merci de confirmer votre présence avant le <?= htmlspecialchars($content['rsvp_date']) ?></p>
            <p>Contact : <?= htmlspecialchars($content['contact_email']) ?> | <?= htmlspecialchars($content['contact_phone']) ?></p>
        </div>
    </div>
</div>

<style>
.business-invitation {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    background: white;
    border: 1px solid #e0e0e0;
    font-family: 'Arial', sans-serif;
}

.invitation-logo {
    text-align: center;
    margin-bottom: 1.5rem;
}

.invitation-logo img {
    max-height: 80px;
}

.invitation-content h1 {
    text-align: center;
    color: var(--primary-color);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.invitation-content h2 {
    text-align: center;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: #333;
}

.event-details {
    background: #f9f9f9;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--primary-color);
}

.event-details p {
    margin-bottom: 0.5rem;
}

.invitation-message {
    line-height: 1.6;
    margin-bottom: 2rem;
}

.rsvp {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
    font-size: 0.9rem;
    text-align: center;
}

@media print {
    .business-invitation {
        border: none;
        padding: 0;
    }
}
</style>