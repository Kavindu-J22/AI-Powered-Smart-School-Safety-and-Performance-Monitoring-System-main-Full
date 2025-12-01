<footer class="footer position-absolute bottom-2 py-2 w-100">
    <div class="container">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 text-center">
                <div class="copyright text-center text-sm text-white text-lg-center">
                    ©
                    <script>
                        document.write(new Date().getFullYear())
                    </script>,
                    made with <i class="fa fa-heart" aria-hidden="true"></i> by
                    <a href="{{ env('APP_OWNERSHIP_URL') }}" class="text-white"
                        target="_blank">{{ env('APP_OWNERSHIP') }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>
