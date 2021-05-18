<?php

namespace App\Http\Controllers;

use Exception;
use Flagship\Hit\Event;
use Flagship\Hit\Item;
use Flagship\Hit\Page;
use Flagship\Hit\Screen;
use Flagship\Hit\Transaction;
use Flagship\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HitController extends Controller
{
    const PAGE        = "PAGE";
    const SCREEN      = "SCREEN";
    const EVENT       = "EVENT";
    const TRANSACTION = "TRANSACTION";
    const ITEM        = "ITEM";

    private function getHit($data)
    {
        $hit = null;
        switch ($data['t']) {
            case self::PAGE:
                $hit = new Page($data['dl']);
                break;
            case self::SCREEN:
                $hit = new Screen($data['dl']);
                break;
            case self::EVENT:
                $hit = new Event($data['ec'], $data['ea']);
                if (isset($data['ev'])) {
                    $hit->setEventValue($data['ev']);
                }
                if (isset($data['el'])) {
                    $hit->setEventLabel($data['el']);
                }
                break;

            case self::TRANSACTION:
                $hit = new Transaction($data['tid'], $data['ta']);
                if (isset($data['icn'])) {
                    $hit->setItemCount($data['icn']);
                }
                if (isset($data['pm'])) {
                    $hit->setPaymentMethod($data['pm']);
                }
                if (isset($data['sm'])) {
                    $hit->setShippingMethod($data['sm']);
                }
                if (isset($data['tc'])) {
                    $hit->setCurrency($data['tc']);
                }
                if (isset($data['tcc'])) {
                    $hit->setCouponCode($data['tcc']);
                }
                if (isset($data['tr'])) {
                    $hit->setTotalRevenue($data['tr']);
                }
                if (isset($data['ts'])) {
                    $hit->setShippingCosts($data['ts']);
                }
                if (isset($data['tt'])) {
                    $hit->setTaxes($data['tt']);
                }
                break;
            case self::ITEM:
                $hit = new Item($data['tid'], $data['ic'], $data['in']);
                if (isset($data['ip'])) {
                    $hit->setItemPrice($data['ip']);
                }
                if (isset($data['iq'])) {
                    $hit->setItemQuantity($data['iq']);
                }
                if (isset($data['iv'])) {
                    $hit->setItemCategory($data['iv']);
                }
        }
        return $hit;
    }

    public function sendHit(Request $request, Visitor $visitor)
    {
        try {
            $data = $this->hitValidation($request);

            $hit = $this->getHit($data);

            $visitor->sendHit($hit);

            return response()->json($visitor->getConfig());
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->errors()], 422);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    private function hitValidation(Request $request)
    {
        return $this->validate($request, [
            't' => ['required', 'string', Rule::in([
                self::ITEM, self::EVENT,
                self::TRANSACTION, self::SCREEN,
                self::PAGE
            ])],
            'ea' => ['string', Rule::requiredIf(
                $request->get('t') == self::EVENT
            )],
            'ec' => ['string',Rule::requiredIf(
                $request->get('t') == self::EVENT
            )],
            'ev' => ['nullable'],
            'el' => ['nullable'],
            'tid' => [Rule::requiredIf(
                $request->get('t') == self::TRANSACTION ||
                $request->get('t') == self::ITEM
            )],
            'ta' => [Rule::requiredIf(
                $request->get('t') == self::TRANSACTION
            )],
            'icn' => 'nullable',
            'pm' => 'nullable',
            'sm' => 'nullable',
            'tc' => 'nullable',
            'tcc' => 'nullable',
            'tr' => 'nullable|numeric',
            'ts' => 'nullable|numeric',
            'tt' => 'nullable|numeric',
            'ic' => ['string',Rule::requiredIf(
                $request->get('t') == self::ITEM
            )],
            'in' => ['string',Rule::requiredIf(
                $request->get('t') == self::ITEM
            )],
            'ip' => 'nullable|numeric',
            'iq' => 'nullable|numeric',
            'iv' => 'nullable|string',
            'dl' => [Rule::requiredIf(
                $request->get('t') == self::PAGE ||
                $request->get('t') === self::SCREEN
            )]
        ]);
    }
}
