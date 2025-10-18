<?php include 'includes/header.php'; ?>

<div class="container">
    <header class="hero-section">
        <nav class="navbar">
            <div class="nav-brand">
                <h1>ሰዋሰው</h1>
                <span>Sewasew - Chat, Our Way</span>
            </div>
            <div class="nav-links">
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Sign Up</a>
            </div>
        </nav>

        <div class="hero-content">
            <div class="hero-text">
                <h2>የኛ የሆነ መስመር ላይ ሰዋሰው</h2>
                <p>Experience messaging built for Ethiopian culture with Amharic support, traditional features, and community-focused tools.</p>
                
                <div class="feature-list">
                    <div class="feature">
                        <span class="icon">🇪🇹</span>
                        <span>Amharic First Design</span>
                    </div>
                    <div class="feature">
                        <span class="icon">☕</span>
                        <span>Buna Time Chat Rooms</span>
                    </div>
                    <div class="feature">
                        <span class="icon">🎤</span>
                        <span>Voice Messages</span>
                    </div>
                    <div class="feature">
                        <span class="icon">👥</span>
                        <span>Community Groups</span>
                    </div>
                </div>

                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-large btn-primary">Start Chatting Now</a>
                    <a href="#features" class="btn btn-large btn-outline">Learn More</a>
                </div>
            </div>
            
            <div class="hero-image">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        <!-- Mock chat interface -->
                        <div class="mock-chat">
                            <div class="mock-message received">
                                <strong>ሰላም! 😊</strong>
                                <p>እንዴት ነህ?</p>
                            </div>
                            <div class="mock-message sent">
                                <strong>እኔ ደህና ነኝ!</strong>
                                <p>በሰዋሰው እየተወያይን ነው! 🇪🇹</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="features" class="features-section">
        <h2>Unique Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">☕</div>
                <h3>Buna Time</h3>
                <p>Temporary chat rooms that expire, just like a traditional coffee ceremony gathering.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🗣️</div>
                <h3>Amharic Voice</h3>
                <p>Speak in Amharic and convert to text with our advanced voice recognition.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Ethiopian Calendar</h3>
                <p>Integrated Ethiopian calendar with holiday reminders and events.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3>Community First</h3>
                <p>Built-in support for Idir, Equb, and Mahiber community groups.</p>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>