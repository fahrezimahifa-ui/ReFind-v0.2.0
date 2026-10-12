# ReFind — presentation photo credits

These stock photographs illustrate the classroom demonstration records. The close-up evidence images are **crops of the listed source photos**, prepared to demonstrate the separate ownership-evidence collection and staff image viewer.

All application images are stored locally. Opening ReFind does not load photos from Pexels or Wikimedia; the links below identify the original sources.

| Subject / local prepared files | Photographer | Original source | License |
| --- | --- | --- | --- |
| Headphones: `headphones-overview.jpg`, `headphones-headband.jpg`, `headphones-earcup.jpg`, `headphones-cable.jpg` | Alex Kinkate | [Black Corded Headset — Pexels photo 205926](https://www.pexels.com/photo/black-corded-headset-205926/) | [Pexels License](https://www.pexels.com/license/) |
| Leather satchel: `satchel-overview.jpg`, `satchel-straps.jpg`, `satchel-buckle.jpg`, `satchel-flap.jpg`, `satchel-strap-clip.jpg`, `satchel-adjuster.jpg` | ge yonk | [Orange and Black Leather Satchel Bag — Pexels photo 1152077](https://www.pexels.com/photo/orange-and-black-leather-satchel-bag-1152077/) | [Pexels License](https://www.pexels.com/license/) |
| Wallet: `wallet-overview.jpg`, `wallet-stitching.jpg` | Lukas Blazek (goumbik) | [Brown Leather Wallet — Pexels photo 915917](https://www.pexels.com/photo/brown-leather-wallet-915917/) | [Pexels License](https://www.pexels.com/license/) |
| Calculator: `calculator-overview.jpg` | Raimond Spekking | [Casio fx-991ES-0622.jpg — Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Casio_fx-991ES-0622.jpg) | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/) |
| Umbrella: `umbrella-overview.jpg` | Swethasakthivel | [Folded Umbrella.jpg — Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Folded_Umbrella.jpg) | [CC0 1.0](https://creativecommons.org/publicdomain/zero/1.0/) |

## Local preparation

- Source photos were oriented from EXIF, resized, placed on a neutral background, and JPEG-compressed for the desktop cards/gallery.
- Detail images were cropped to show the headphone badge/cable, satchel strap hardware, or wallet stitching. No new physical identifying marks were added.
- Every prepared image is **1200 × 900px**, at most **180,000 bytes**.
- The resized/framed calculator derivative remains available under **CC BY-SA 4.0**, with attribution and license link above.
- Prepared originals are in `tools/demo-photos/`. Apache blocks that tools directory; application copies are served through the existing photo endpoints from `storage/items/`, `storage/evidence/`, and `storage/claims/`.


## ReFind 0.2.0

The prepared sources above are reused as synthetic classroom examples. Runtime copies have generated filenames in separate public/private collections. New demo IDs are recorded privately by tools/seed-student-demo.php; the old database/photo mapping is not imported.

## B06 expanded classroom dataset

The same credited subjects illustrate the40 synthetic Lost/Found reports and12 mock claims. Reuse across scenario codes C01–C19 is deliberate; these are classroom illustrations, not photographs of40 independently recovered campus objects. Public ITEM, private Lost OWNERSHIP and claim-proof copies have separate generated paths/authorization. `tools/seed-expanded-demo.php` simulates review/pickup outcomes through real business functions and stores bookmarks privately. Calculator derivatives retain the CC BY-SA4.0 attribution/license above.
