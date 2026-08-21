    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="brand-logo" style="margin-bottom: 14px;">
                        <i class="fa-solid fa-film" style="color: #6366f1;"></i>
                        <span>CINEMA</span> WORLD
                    </div>
                    <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                        Experience the magic of cinema with cutting-edge 4K laser projection, Dolby Atmos 360° immersive audio, and premium recliner seating.
                    </p>
                    <div style="display: flex; gap: 12px;">
                        <a href="#" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0;"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0;"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0;"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0;"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Now Showing</a></li>
                        <li><a href="index.php#upcoming"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Coming Soon</a></li>
                        <li><a href="my_bookings.php"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> My Tickets</a></li>
                        <li><a href="profile.php"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Profile Account</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Experiences</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> IMAX 3D Experience</a></li>
                        <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Dolby Atmos Hall</a></li>
                        <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> VIP Recliner Lounge</a></li>
                        <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Gourmet Concessions</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Customer Care</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">
                        <i class="fa-solid fa-location-dot" style="color: var(--accent); margin-right: 6px;"></i> Durbar Marg, Kathmandu, Nepal
                    </p>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">
                        <i class="fa-solid fa-phone" style="color: var(--accent); margin-right: 6px;"></i> +977 01-4455667 / 9801234567
                    </p>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                        <i class="fa-solid fa-envelope" style="color: var(--accent); margin-right: 6px;"></i> support@cinemaworld.com
                    </p>
                    <span style="display:inline-block; font-size:11px; padding:4px 10px; background:rgba(16,185,129,0.1); color:#34d399; border-radius:20px; border:1px solid rgba(16,185,129,0.3);">
                        <i class="fa-solid fa-circle-check"></i> 100% Instant E-Tickets
                    </span>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Cinema World Entertainment Ltd. All rights reserved. Designed for ultimate cinema lovers.</p>
            </div>
        </div>
    </footer>

    <!-- Trailer Modal -->
    <div id="trailerModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:20px;">
        <div style="position:relative; width:100%; max-width:850px; background:#0f172a; border-radius:16px; border:1px solid rgba(255,255,255,0.1); overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8);">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.08);">
                <h4 id="trailerModalTitle" style="color:#fff; font-size:18px;">Movie Trailer</h4>
                <button onclick="closeTrailerModal()" style="background:none; border:none; color:#94a3b8; font-size:22px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden;">
                <iframe id="trailerIframe" style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
        </div>
    </div>

    <script>
    function openTrailer(title, url) {
        if (!url) return;
        document.getElementById('trailerModalTitle').innerText = title + ' - Official Trailer';
        // Convert youtube watch url to embed if needed
        let embedUrl = url;
        if (url.includes('watch?v=')) {
            embedUrl = url.replace('watch?v=', 'embed/') + '?autoplay=1';
        } else if (url.includes('youtu.be/')) {
            let id = url.split('youtu.be/')[1];
            embedUrl = 'https://www.youtube.com/embed/' + id + '?autoplay=1';
        }
        document.getElementById('trailerIframe').src = embedUrl;
        document.getElementById('trailerModal').style.display = 'flex';
    }

    function closeTrailerModal() {
        document.getElementById('trailerIframe').src = '';
        document.getElementById('trailerModal').style.display = 'none';
    }
    
    // Close modal on escape key or outer click
    window.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeTrailerModal(); });
    document.getElementById('trailerModal').addEventListener('click', (e) => {
        if(e.target.id === 'trailerModal') closeTrailerModal();
    });
    </script>
</body>
</html>
