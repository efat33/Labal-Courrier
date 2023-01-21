<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <h3 class="elementor-heading-title elementor-size-default">Frequently Asked Questions</h3>
                <div class="accordion accordion-flush mb-3 mt-4" id="accordionFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <?= esc_html_e("Do I have to pay customs fees?", "labal-courrier") ?>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <?= sprintf(__("International shipments may be subject to customs duties in the destination country. You should check the customs laws of your destination country. For shipments to France, we have gathered the information here (%s)", "labal-courrier"), esc_url(site_url('droits-de-douanes-colis'))) ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <?= esc_html_e("What is the volumetric weight ?", "labal-courrier") ?>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <?= __("The volumetric weight = (height x length x width in cm) /5000 <br>For a 20 kgs suitcase with the following dimensions 70/55/35cm <br>Weight volume = 70x55x35 / 5000 = 26,95 kg <br>The weight charged for the shipment of your suitcase will be 27 kg.", "labal-courrier") ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                <?= esc_html_e("Do I need to print any documents?", "labal-courrier") ?>
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <?= esc_html_e("After the payment, we will send you the shipping document that you must print and give to the carrier. ", "labal-courrier") ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>