<div id="receiptPrintArea">

    <h5 class="text-center">BASHIR MADAKI</h5>
    <p class="text-center mb-2">Transportation Payment Receipt</p>

    <table class="table table-bordered">
        <tr>
            <th>Name</th>
            <td><?= $person['fullname'] ?></td>
        </tr>
        <tr>
            <th>Driver</th>
            <td><?= $transport['driver_name'] ?></td>
        </tr>
        <tr>
            <th>Motor No</th>
            <td><?= $transport['bossno'] ?></td>
        </tr>
        <tr>
            <th>Surviving Animals</th>
            <td><?= $person['total_animal'] ?></td>
        </tr>
        <tr>
            <th>Cost per Animal</th>
            <td>₦<?= number_format($costPerAnimal, 2) ?></td>
        </tr>
        <tr>
            <th>Expected Amount</th>
            <td>₦<?= number_format($expected) ?></td>
        </tr>
        <tr>
            <th>1st Payment</th>
            <td>₦<?= number_format($person['first_payment']) ?></td>
        </tr>
        <tr>
            <th>2nd Payment</th>
            <td>₦<?= number_format($person['second_payment']) ?></td>
        </tr>
        <tr>
            <th>3rd Payment</th>
            <td>₦<?= number_format($person['third_payment']) ?></td>
        </tr>
        <tr>
            <th>Total Paid</th>
            <td><strong>₦<?= number_format($person['total']) ?></strong></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <?php if($balance > 0): ?>
                    <?= number_format($balance) ?> Remaining
                <?php elseif($balance < 0): ?>
                    <?= number_format(abs($balance)) ?> Overpaid
                <?php else: ?>
                    Cleared
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <small>Date: <?= date('d M Y') ?></small>

</div>
