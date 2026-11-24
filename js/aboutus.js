/* =====================================================
   ALAM INDAH - aboutus.js
   Dipakai di aboutus.html (setelah bootstrap.bundle.js)

   1. Navbar: ganti gaya (.is-scrolled) saat halaman di-scroll
   2. Gallery: strip foto berjalan ke kiri pelan-pelan
      - foto di posisi fokus = foto terpilih (lebih tinggi, berbingkai)
      - background section otomatis memakai foto terpilih
      - berputar tanpa putus; jeda saat kursor di atas strip;
        klik foto untuk langsung memilihnya

   Menambah foto: cukup tambah <figure class="gallery-item"> di HTML.
===================================================== */



/* =====================================================
   2. GALLERY
===================================================== */

(function () {
    var gallery = document.getElementById('gallery');
    var track = document.getElementById('galleryTrack');
    if (!gallery || !track) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ===== Atur perilaku animasi di sini ===== */
    var CONFIG = {
        interval: 2000,                         // jarak waktu antar geseran (ms). Makin besar = makin pelan
        slideDuration: reduceMotion ? 0 : 1300, // lama satu geseran ke kiri (ms). Makin besar = makin lambat
        pauseOnHover: true                      // berhenti saat kursor di atas strip
    };

    /* Posisi foto terpilih, sebagai bagian dari lebar layar */
    function focusRatio(w) {
        if (w < 576) return 0.2;
        if (w < 992) return 0.24;
        return 0.3;
    }

    /* ----- ambil data foto dari HTML ----- */
    var source = Array.prototype.map.call(track.querySelectorAll('.gallery-item'), function (el) {
        var img = el.querySelector('img');
        return {
            src: img ? img.getAttribute('src') : '',
            alt: img ? img.alt : '',
            title: el.dataset.title || ''
        };
    }).filter(function (s) { return s.src; });

    var n = source.length;
    if (n < 2) return;

    var layers = gallery.querySelectorAll('.gallery-bg-layer');
    var layerOn = 0;

    var nodes = [];        // urutan elemen di strip
    var A = 0;             // indeks elemen terpilih di dalam nodes
    var step = 0;          // jarak antar foto (lebar + celah)
    var base = 0;          // posisi translateX normal
    var cursor = 0;        // nomor foto yang sedang terpilih
    var busy = false;
    var paused = false;
    var visible = false;
    var timer = null;

    function pad(v) { return v < 10 ? '0' + v : '' + v; }

    /* ----- background = foto terpilih (crossfade) ----- */
    function setBg(src, instant) {
        if (!layers.length) return;
        var next = layers[1 - layerOn];
        var cur = layers[layerOn];

        function apply() {
            next.style.backgroundImage = 'url("' + src + '")';
            next.classList.add('is-on');
            cur.classList.remove('is-on');
            layerOn = 1 - layerOn;
        }

        if (instant) {
            cur.style.backgroundImage = 'url("' + src + '")';
            cur.classList.add('is-on');
            return;
        }

        var img = new Image();
        img.onload = apply;
        img.onerror = apply;
        img.src = src;
    }

    /* ----- elemen satu foto ----- */
    function fillNode(node, i) {
        var s = source[i];
        node.dataset.i = i;
        node.querySelector('.gallery-photo').style.setProperty('--photo', 'url("' + s.src + '")');
        node.querySelector('img').src = s.src;
        node.querySelector('img').alt = s.alt;
        node.querySelector('.gallery-caption').textContent =
            pad(i + 1) + ' / ' + pad(n) + (s.title ? '  ·  ' + s.title : '');
    }

    function makeNode(i) {
        var fig = document.createElement('figure');
        fig.className = 'gallery-item';
        fig.innerHTML =
            '<div class="gallery-photo"><img alt=""></div>' +
            '<figcaption class="gallery-caption"></figcaption>';
        fillNode(fig, i);
        return fig;
    }

    function setX(x, animate) {
        track.style.transition = animate
            ? 'transform ' + CONFIG.slideDuration + 'ms cubic-bezier(0.65, 0, 0.35, 1)'
            : 'none';
        track.style.transform = 'translate3d(' + x + 'px, 0, 0)';
    }

    /* ----- susun strip (saat load & resize) ----- */
    function build() {
        track.innerHTML = '';
        nodes = [];

        // ukur lebar foto & jaraknya
        var t1 = makeNode(0);
        var t2 = makeNode(1);
        track.appendChild(t1);
        track.appendChild(t2);
        step = t2.offsetLeft - t1.offsetLeft;

        var W = gallery.clientWidth;
        var focusX = W * focusRatio(W);

        A = Math.ceil(focusX / step) + 1;                    // foto di kiri terpilih (supaya kiri terisi)
        var right = Math.ceil((W - focusX) / step) + 2;      // foto di kanan (supaya kanan terisi)
        var total = A + right + 1;

        track.innerHTML = '';
        for (var p = 0; p < total; p++) {
            var idx = (((cursor + p - A) % n) + n) % n;
            var node = makeNode(idx);
            track.appendChild(node);
            nodes.push(node);
        }

        base = focusX - A * step;
        setX(base, false);
        nodes[A].classList.add('is-active');
    }

    /* ----- geser k foto (k > 0 ke kiri, k < 0 ke kanan) ----- */
    function shift(k) {
        if (busy || k === 0) return;
        busy = true;

        var forward = k > 0;
        var count = Math.abs(k);
        var j, node, ref;

        if (!forward) {
            // pindahkan foto dari ujung kanan ke ujung kiri dulu
            for (j = 0; j < count; j++) {
                node = nodes.pop();
                ref = +nodes[0].dataset.i;
                fillNode(node, (ref - 1 + n) % n);
                track.insertBefore(node, track.firstChild);
                nodes.unshift(node);
            }
            setX(base - count * step, false);
            void track.offsetWidth;   // paksa browser menerapkan posisi sebelum animasi
        }

        var oldIdx = forward ? A : A + count;
        var newIdx = forward ? A + count : A;

        nodes[oldIdx].classList.remove('is-active');
        nodes[newIdx].classList.add('is-active');

        cursor = +nodes[newIdx].dataset.i;
        setBg(source[cursor].src, false);

        setX(forward ? base - count * step : base, true);

        setTimeout(function () {
            if (forward) {
                // foto yang sudah lewat di kiri dipindah ke ujung kanan
                for (var m = 0; m < count; m++) {
                    var first = nodes.shift();
                    var last = +nodes[nodes.length - 1].dataset.i;
                    fillNode(first, (last + 1) % n);
                    track.appendChild(first);
                    nodes.push(first);
                }
            }
            setX(base, false);
            busy = false;
        }, CONFIG.slideDuration + 60);
    }

    /* ----- jalan otomatis ----- */
    function schedule() {
        clearTimeout(timer);
        if (reduceMotion) return;
        timer = setTimeout(function () {
            if (!paused && visible && !document.hidden) shift(1);
            schedule();
        }, CONFIG.interval);
    }

    // klik foto = langsung pilih foto itu
    track.addEventListener('click', function (e) {
        var fig = e.target.closest('.gallery-item');
        if (!fig) return;
        var k = nodes.indexOf(fig) - A;
        if (k !== 0) {
            shift(k);
            schedule();
        }
    });

    if (CONFIG.pauseOnHover) {
        var strip = gallery.querySelector('.gallery-strip');
        strip.addEventListener('mouseenter', function () { paused = true; });
        strip.addEventListener('mouseleave', function () { paused = false; });
    }

    /* ----- mulai ----- */
    build();
    setBg(source[cursor].src, true);

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            visible = entries[0].isIntersecting;
        }, { threshold: 0.35 }).observe(gallery);
    } else {
        visible = true;
    }

    schedule();

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (!busy) build();
        }, 200);
    });
})();