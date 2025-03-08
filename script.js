document.addEventListener('DOMContentLoaded', function() {
    const carousel = {
        currentSlide: 0,
        items: document.querySelectorAll('.carousel-items'),
        dots: document.querySelectorAll('.dot'),
        prevButton: document.querySelector('.prev'),
        nextButton: document.querySelector('.next'),
        autoPlayInterval: null,

        init() {
            this.addEventListeners();
            this.startAutoPlay();
        },

        addEventListeners() {
            this.prevButton.addEventListener('click', () => this.prevSlide());
            this.nextButton.addEventListener('click', () => this.nextSlide());

            this.dots.forEach(dot => {
                dot.addEventListener('click', (e) => {
                    const index = parseInt(e.target.getAttribute('data-index'));
                    this.goToSlide(index);
                });
            });
        },

        updateSlide() {
            this.items.forEach(item => item.classList.remove('active'));
            this.dots.forEach(dot => dot.classList.remove('active'));

            this.items[this.currentSlide].classList.add('active');
            this.dots[this.currentSlide].classList.add('active');
        },

        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.items.length;
            this.updateSlide();
            this.resetAutoPlay();
        },

        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.items.length) % this.items.length;
            this.updateSlide();
            this.resetAutoPlay();
        },

        goToSlide(index) {
            this.currentSlide = index;
            this.updateSlide();
            this.resetAutoPlay();
        },

        startAutoPlay() {
            this.autoPlayInterval = setInterval(() => this.nextSlide(), 5000);
        },

        resetAutoPlay() {
            clearInterval(this.autoPlayInterval);
            this.startAutoPlay();
        }
    };

    carousel.init();
});