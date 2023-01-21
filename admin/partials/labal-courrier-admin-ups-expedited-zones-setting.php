<?php

$zoneObj = new UPS_Zone();
$rateCategoryObj = new LC_Rate();


?>
<div class="lc">
    <div class="container">

        <h1 style="font-size: 24px;" class="mb-3 mt-4">Rate Settings</h1>
        <div class="row">
            <div class="col-12">

                <h2 style="font-size: 18px;" class="mb-3 mt-4">Export Rate</h2>
                <div class="row">
                    <div class="col-12">
                        <div class="accordion rate-setting" id="zone_setting">
                            <?php
                            $i = 0;

                            foreach ($zoneObj->get_ups_expedited_export_zones() as $zone_id => $zone) :

                                $existingZoneValues = get_option('ups_expedited_export_rate_' . $zone_id);

                                // Open accordion on page load
                                $show = '';
                                if (isset($_GET['updated_zone'])) {
                                    $show = ($zone_id == $_GET['updated_zone'] && $_GET['type'] == 'export') ? 'show' : '';
                                } else {
                                    $show = ($i < 1) ? 'show' : '';
                                }
                            ?>
                                <div class="card">
                                    <div class="card-header" id="zone<?= $zone_id ?>">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse_e<?= $zone_id ?>" aria-expanded="true" aria-controls="collapse<?= $zone_id ?>">
                                                <?= $zone ?>
                                            </button>
                                        </h2>
                                    </div>

                                    <div id="collapse_e<?= $zone_id ?>" class="collapse <?= $show ?>" aria-labelledby="zone<?= $zone_id ?>" data-parent="#zone_setting">
                                        <div class="card-body">
                                            <form action="<?php echo admin_url() . 'admin-post.php'; ?>" method="POST" data-zone-id="zone<?= $zone_id ?>" class="form-rate form-zone-<?= $zone_id ?>">

                                                <input type="hidden" value="update_ups_ie_lc_rate" name="action" />
                                                <input type="hidden" value="<?= $zone_id ?>" name="zone_id" />
                                                <input type="hidden" value="expedited" name="service_code" />
                                                <input type="hidden" value="export" name="shipment_type" />

                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="row ml-3 mb-3">
                                                            <div class="col-12">
                                                                <h2 style="font-size: 18px;">Export Rate</h2>
                                                            </div>
                                                        </div>
                                                        <?php foreach ($rateCategoryObj->get_import_rate_categories() as $category) : ?>

                                                            <?php
                                                            $coefficient = '';
                                                            if (is_array($existingZoneValues) && isset($existingZoneValues['export_rate']['coefficient'][$category['category_id']])) {
                                                                $coefficient = $existingZoneValues['export_rate']['coefficient'][$category['category_id']] ?? '';
                                                            }
                                                            ?>

                                                            <div class="row ml-3 mb-3">
                                                                <div class="col-12">
                                                                    <h3 class="category-title"><?= $category['title'] ?></h3>
                                                                    <div class="form-row">
                                                                        <div class="col form-group">
                                                                            <label for="">Package type</label>
                                                                            <input type="text" value="<?= $category['package_type'] ?>" class="form-control" readonly />
                                                                        </div>
                                                                        <div class="col form-group">
                                                                            <label for="">Min weight <span class="font-italic">(kg)</span></label>
                                                                            <input type="text" value="<?= $category['min_weight'] ?>" class="form-control" readonly />
                                                                        </div>
                                                                        <div class="col form-group">
                                                                            <label for="">Max weight <span class="font-italic">(kg)</label>
                                                                            <input type="text" value="<?= $category['max_weight'] ?>" class="form-control" readonly />
                                                                        </div>
                                                                        <div class="col form-group">
                                                                            <label for="">Coefficient</label>
                                                                            <input name="export_rate[coefficient][<?= $category['category_id'] ?>]" value="<?= $coefficient ?>" type="text" class="form-control" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>


                                                <div class="row ml-3">
                                                    <div class="col-12">
                                                        <div class="form-row">
                                                            <div class="col form-group">
                                                                <label for="">&nbsp;</label>
                                                                <button class="btn btn-primary float-right" type="submit">Save</button>
                                                                <button class="btn btn-outline-secondary float-right mr-2" type="submit">Reset</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                $i++;
                            endforeach;
                            ?>
                        </div>

                    </div>
                </div>
            </div>


        </div>

        <div class="row">
            <div class="col-12">

                <h2 style="font-size: 18px;" class="mb-3 mt-4">Import Rate</h2>
                <div class="row">
                    <div class="col-12">
                        <div class="accordion rate-setting" id="zone_i_setting">
                            <?php
                            $i = 0;

                            foreach ($zoneObj->get_ups_expedited_import_zones() as $zone_id => $zone) :

                                $existingZoneValues = get_option('ups_expedited_import_rate_' . $zone_id);

                                // Open accordion on page load
                                $show = '';
                                if (isset($_GET['updated_zone'])) {
                                    $show = ($zone_id == $_GET['updated_zone'] && $_GET['type'] == 'import') ? 'show' : '';
                                } else {
                                    $show = ($i < 1) ? 'show' : '';
                                }
                            ?>
                                <div class="card">
                                    <div class="card-header" id="zone<?= $zone_id ?>">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse_i<?= $zone_id ?>" aria-expanded="true" aria-controls="collapse<?= $zone_id ?>">
                                                <?= $zone ?>
                                            </button>
                                        </h2>
                                    </div>

                                    <div id="collapse_i<?= $zone_id ?>" class="collapse <?= $show ?>" aria-labelledby="zone<?= $zone_id ?>" data-parent="#zone_i_setting">
                                        <div class="card-body">
                                            <form action="<?php echo admin_url() . 'admin-post.php'; ?>" method="POST" data-zone-id="zone<?= $zone_id ?>" class="form-rate form-zone-<?= $zone_id ?>">

                                                <input type="hidden" value="update_ups_ie_lc_rate" name="action" />
                                                <input type="hidden" value="<?= $zone_id ?>" name="zone_id" />
                                                <input type="hidden" value="expedited" name="service_code" />
                                                <input type="hidden" value="import" name="shipment_type" />

                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="row ml-3 mb-3">
                                                            <div class="col-12">
                                                                <h2 style="font-size: 18px;">Export Rate</h2>
                                                            </div>
                                                        </div>
                                                        <?php foreach ($rateCategoryObj->get_import_rate_categories() as $category) : ?>

                                                            <?php
                                                            $coefficient = '';
                                                            if (is_array($existingZoneValues) && isset($existingZoneValues['import_rate']['coefficient'][$category['category_id']])) {
                                                                $coefficient = $existingZoneValues['import_rate']['coefficient'][$category['category_id']] ?? '';
                                                            }
                                                            ?>

                                                            <div class="row ml-3 mb-3">
                                                                <div class="col-12">
                                                                    <h3 class="category-title"><?= $category['title'] ?></h3>
                                                                    <div class="form-row">
                                                                        <div class="col form-group">
                                                                            <label for="">Package type</label>
                                                                            <input type="text" value="<?= $category['package_type'] ?>" class="form-control" readonly />
                                                                        </div>
                                                                        <div class="col form-group">
                                                                            <label for="">Min weight <span class="font-italic">(kg)</span></label>
                                                                            <input type="text" value="<?= $category['min_weight'] ?>" class="form-control" readonly />
                                                                        </div>
                                                                        <div class="col form-group">
                                                                            <label for="">Max weight <span class="font-italic">(kg)</label>
                                                                            <input type="text" value="<?= $category['max_weight'] ?>" class="form-control" readonly />
                                                                        </div>
                                                                        <div class="col form-group">
                                                                            <label for="">Coefficient</label>
                                                                            <input name="import_rate[coefficient][<?= $category['category_id'] ?>]" value="<?= $coefficient ?>" type="text" class="form-control" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>


                                                <div class="row ml-3">
                                                    <div class="col-12">
                                                        <div class="form-row">
                                                            <div class="col form-group">
                                                                <label for="">&nbsp;</label>
                                                                <button class="btn btn-primary float-right" type="submit">Save</button>
                                                                <button class="btn btn-outline-secondary float-right mr-2" type="submit">Reset</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                $i++;
                            endforeach;
                            ?>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>
</div>