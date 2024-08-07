<?PHP

	header("Content-type: text/css; charset=utf-8");
	$engine_url = $_GET['engine_url'];

?>
/**
 * Farbtastic Color Picker 1.2
 * 짤 2008 Steven Wittens
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
 */
.farbtastic {
  position: relative;
}
.farbtastic * {
  position: absolute;
  cursor: crosshair;
}
.farbtastic, .farbtastic .wheel {
  top: 47px;
  width: 195px;
  height: 195px;
}
.farbtastic .color, .farbtastic .overlay {
  top: 47px;
  left: 47px;
  width: 101px;
  height: 101px;
}
.farbtastic .wheel {
  top: -1px;
  background: url('<?=$engine_url?>/_engine/common/colorpicker/wheel.png') no-repeat;
  width: 195px;
  height: 195px;
}
.farbtastic .overlay {
  background: url('<?=$engine_url?>/_engine/common/colorpicker/mask.png') no-repeat;
}
.farbtastic .marker {
  width: 17px;
  height: 17px;
  margin: -8px 0 0 -8px;
  overflow: hidden;
  background: url('<?=$engine_url?>/_engine/common/colorpicker/marker.png') no-repeat;
}