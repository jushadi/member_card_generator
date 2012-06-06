<?php
/**
 * SENAYAN application printable data configuration
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// label print settings
/* measurement in cm */
$page_margin = 0.2;
$items_per_row = 3;
$items_margin = 0.05;
$box_width = 8;
$box_height = 3.3;
$include_header_text = 1; // change to 0 if dont want to use header in each label
$header_text = ''; // keep empty if you want to use Library Name as a header text
$fonts = "Arial, Verdana, Helvetica, 'Trebuchet MS'";
$font_size = 11;
$border_size = 1; // in pixels

// item barcode print settings
/* measurement in cm */
$barcode_page_margin = 0.2;
$barcode_items_per_row = 3;
$barcode_items_margin = 0.1;
$barcode_box_width = 7;
$barcode_box_height = 5;
$barcode_include_header_text = 1; // change to 0 if dont want to use header in each barcode
$barcode_cut_title = 50; // maximum characters in title to appear in each barcode. change to 0 if you dont want the title cutted
$barcode_header_text = ''; // keep empty if you want to use Library Name as a header text
$barcode_fonts = "Arial, Verdana, Helvetica, 'Trebuchet MS'"; // font to use
$barcode_font_size = 11;
$barcode_scale = 70; // barcode scale in percent relative to box width and height
$barcode_border_size = 1; // in pixels

// member card print settings
/* measurement in cm */
$card_page_margin = 0.2;
$card_items_margin = 0.1;
$card_items_per_row = 1; //

//Jushadi Arman Saz
/* measurement in cm*/
$card_factor = "37.795275591"; //cm to px

// Items Settings
// change to 0 if dont want to use selected items 
$card_include_id_label = 1; // no anggota
$card_include_name_label = 1; // nama anggota
$card_include_pin_label = 1; // nim
$card_include_inst_label = 0; // prodi
$card_include_email_label = 0; // email
$card_include_address_label = 1; // alamat
$card_include_barcode_label = 1; // barcode
$card_include_expired_label = 1; // expired

// Cardbox Settings
$card_box_width = 8.6;
$card_box_height = 5.4;

// Logo Setting  
$card_logo = "logo.png";
$card_front_logo_width = "";
$card_front_logo_height = "";
$card_front_logo_left = "";
$card_front_logo_top = "";
$card_back_logo_width = "";
$card_back_logo_height = "";
$card_back_logo_left = "";
$card_back_logo_top = "";

// Photo Settings
$card_photo_left = "";
$card_photo_top = "";
$card_photo_width = 1.5;
$card_photo_height = 1.8;


// Header Settings
$card_front_header1_text = "KARTU ANGGOTA PERPUSTAKAAN<br />PROGRAM PASCASARJANA"; // use <br /> tag to make another line
$card_front_header1_font_size = "12";
$card_front_header2_text = "UNIVERSITAS NEGERI MAKASSAR";
$card_front_header2_font_size = "12";
$card_back_header1_text = "PROGRAM PASCASARJANA<br />UNIVERSITAS NEGERI MAKASSAR";
$card_back_header1_font_size = "12";
$card_back_header2_text = "Kampus UNM Gunung Sari Baru, Jl. Bonto Langkasa, Makassar - 90222<br />Telp. (0411) 830368, Fax. (0411) 855288, e-mail: pasca@unm.ac.id, website: pps.unm.ac.id";
$card_back_header2_font_size = "5";
$card_header_color = "#0066FF"; //e.g. :#0066FF, green, etc.

//biodata settings
$card_bio_font_size = "11";
$card_bio_font_weight = "bold";
$card_bio_label_width = "100";

// Stamp Settings
$card_lokasi_stempel = "Makassar";
$card_jabatan_stempel = "Direktur";
$card_pejabat_stempel = "Jushadi Arman Saz";
$card_nip_pejabat_stempel = "NIP. 19871002 201210 1 001";
$card_gambar_stempel = "stempel.png"; // stamp image, use transparent image
$card_gambar_ttd_stempel = "ttd.png"; // sign picture, use transparent image
$card_stamp_left = "";
$card_stamp_top = "";
$card_stamp_width = "";
$card_stamp_height = "";

//expired
$card_exp_left = "";
$card_exp_top = "";
$card_exp_width = "";
$card_exp_height = "";

// Barcode Setting
$card_barcode_scale = 100; // barcode scale in percent relative to box width and height
$card_barcode_left = "";
$card_barcode_top = "";
$card_barcode_width = "";
$card_barcode_height = "";

// Rules
$card_rules = "<ul>
<li>Kartu ini diterbitkan oleh Perpustakaan PPs UNM. Segala penggunaan kartu diatur oleh Perpustakaan PPs UNM sesuai ketentuan dan syarat yang berlaku.</li>
<li>Bila menemukan kartu ini mohon mengembalikan ke Perpustakaan Program Pascasarjana UNM.</li>
</ul>";
$card_rules_font_size = "8";

// address
$card_address = "Perpustakaan Program Pascasarjana UNM<br />website: http://dlibpps.unm.ac.id, email : pustakawan@unm.ac.id";
$card_address_font_size = "7";
$card_address_left = "";
$card_address_top = "";


// barcode generator print settings
$barcodegen_box_width = 6;
$barcodegen_page_margin = 0.2;
$barcodegen_items_margin = 0.05;
$barcodegen_include_border = 0;

/* Receipt Printing */
$receipt_width = '15cm';
$receipt_font = 'serif';
$receipt_color = '#000';
$receipt_margin = '5px';
$receipt_padding = '5px';
$receipt_border = '1px dashed #000';
$receipt_fontSize = '7pt';
$receipt_header_fontSize = '8pt';
$receipt_titleLength = 100;

?>
