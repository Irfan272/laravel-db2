<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewController extends Controller
{
    public function index(){
        $results = DB::connection("db2_ibm")->table('ITXVIEWKK_UNTUK_MEMOPENTING_PPC as i')
        ->distinct()
        ->select(
            'i.ITEMTYPEAFICODE',
            'i.ITEMTYPEAFICODE_YND',
            'i.CREATIONDATETIME_PRODORDER as ORDERDATE',
            DB::raw("ip.LANGGANAN || '|' || 
                CASE
                    WHEN ip.BUYER IS NULL THEN 'Data tidak ditemukan, silahkan periksa kembali'
                    ELSE ip.BUYER
                END AS PELANGGAN"),
            'ip.ORDPRNCUSTOMERSUPPLIERCODE',
            'ip.LANGGANAN as LANGGANAN',
            'ip.BUYER as BUYER',
            'i.PROJECTCODE as NO_ORDER',
            'ik.EXTERNALREFERENCE as NO_PO',
            DB::raw("TRIM(i.SUBCODE02) as SUBCODE02"),
            DB::raw("TRIM(i.SUBCODE03) as SUBCODE03"),
            DB::raw("TRIM(i.SUBCODE01) || '-' || TRIM(i.SUBCODE02) || '-' || TRIM(i.SUBCODE03) || '-' || TRIM(i.SUBCODE04) || '-' || TRIM(i.SUBCODE05) || '-' || TRIM(i.SUBCODE06) || '-' || TRIM(i.SUBCODE07) || '-' || TRIM(i.SUBCODE08) as KETERANGAN_PRODUCT"),
            'i.ITEMDESCRIPTION as JENIS_KAIN',
            'i.WARNA as WARNA',
            'i.SUBCODE05 as NO_WARNA',
            'i.DELIVERYDATE as DELIVERY',
            'p.USEDUSERPRIMARYQUANTITY as QTY_BAGIKAIN',
            'p.USEDUSERSECONDARYQUANTITY as QTY_BAGIKAIN_YD_MTR',
            'in2.USERPRIMARYQUANTITY as NETTO',
            DB::raw("CASE
                        WHEN DAYS(now()) - DAYS(Timestamp_Format(i.DELIVERYDATE, 'YYYY-MM-DD')) < 0 THEN 0
                        ELSE DAYS(now()) - DAYS(Timestamp_Format(i.DELIVERYDATE, 'YYYY-MM-DD'))
                    END AS DELAY"),
            'i.PRODUCTIONORDERCODE as NO_KK',
            'i.DEAMAND as DEMAND',
            'i.LOT',
            'i.ORDERLINE',
            DB::raw("TRIM(i.PROGRESSSTATUS) as PROGRESSSTATUS"),
            DB::raw("CASE
                        WHEN u.LONGDESCRIPTION IS NULL THEN ''
                        ELSE u.LONGDESCRIPTION
                    END || CASE
                        WHEN a4.VALUESTRING IS NULL THEN ''
                        ELSE a4.VALUESTRING
                    END AS KETERANGAN"),
            'i.ABSUNIQUEID_DEMAND',
            'i.REQUIREDDUEDATE',
            'a2.VALUESTRING as OriginalPDCode',
            'a3.VALUESTRING as SplittedFrom',
            'i.CREATIONDATETIME_SALESORDER',
            'i.PROGRESSSTATUS_DEMAND',
            'i.EXTERNALITEM as NO_ITEM',
            'i2.LEBAR as LEBAR'
        )
        ->leftJoin(DB::raw('(SELECT ik.CODE, ik.EXTERNALREFERENCE, ik.PROJECTCODE, ik.ORIGDLVSALORDERLINEORDERLINE FROM ITXVIEW_KGBRUTO ik) as ik'), function ($join) {
            $join->on('ik.PROJECTCODE', '=', 'i.PROJECTCODE')
                ->on('ik.ORIGDLVSALORDERLINEORDERLINE', '=', 'i.ORIGDLVSALORDERLINEORDERLINE')
                ->on('ik.CODE', '=', 'i.DEAMAND');
        })
        ->leftJoin(DB::raw('(SELECT p.ORDERCODE, p.USEDUSERPRIMARYQUANTITY, p.USEDUSERSECONDARYQUANTITY FROM ITXVIEW_RESERVATION_KK p) as p'), 'p.ORDERCODE', '=', 'i.PRODUCTIONDEMANDCODE')
        ->leftJoin(DB::raw('(SELECT a.UNIQUEID, a.FIELDNAME, a.VALUESTRING FROM ADSTORAGE a) as a'), function ($join) {
            $join->on('a.UNIQUEID', '=', 'i.ABSUNIQUEID_DEMAND')
                ->where('a.FIELDNAME', '=', 'DefectTypeCode');
        })
        // ... (continue with other left joins)
    
        ->whereNotIn('i.ITEMTYPEAFICODE', ['KGF'])
        ->whereBetween('i.DELIVERYDATE', ['2024-01-01', '2024-01-19'])
        ->get();
    }
}
