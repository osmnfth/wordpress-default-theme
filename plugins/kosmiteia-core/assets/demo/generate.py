# -*- coding: utf-8 -*-

import math
import os
import struct
import zlib

HERE = os.path.dirname(os.path.abspath(__file__))

NAVY = (14, 26, 43)
BLUE = (11, 61, 145)
BLUE_LIGHT = (18, 85, 158)
GOLD = (138, 106, 31)
RED = (122, 31, 43)
TEAL = (13, 92, 99)
PLUM = (63, 36, 92)


def write_png(path, width, height, pixels, alpha=False):
    channels = 4 if alpha else 3
    raw = bytearray()
    stride = width * channels
    for y in range(height):
        raw.append(0)
        raw += pixels[y * stride:(y + 1) * stride]

    def chunk(tag, data):
        return (struct.pack('>I', len(data)) + tag + data +
                struct.pack('>I', zlib.crc32(tag + data) & 0xffffffff))

    png = b'\x89PNG\r\n\x1a\n'
    png += chunk(b'IHDR', struct.pack('>IIBBBBB', width, height, 8, 6 if alpha else 2, 0, 0, 0))
    png += chunk(b'IDAT', zlib.compress(bytes(raw), 9))
    png += chunk(b'IEND', b'')
    with open(path, 'wb') as handle:
        handle.write(png)
    return len(png)


def mix(a, b, t):
    t = max(0.0, min(1.0, t))
    return tuple(int(round(a[i] + (b[i] - a[i]) * t)) for i in range(3))


def scene(width, height, top, bottom, accent, seed):
    pixels = bytearray(width * height * 3)
    skyline = []
    rnd = seed

    def rand():
        nonlocal rnd
        rnd = (rnd * 1103515245 + 12345) % 2147483648
        return rnd / 2147483648.0

    columns = 9
    for i in range(columns):
        skyline.append(0.35 + rand() * 0.4)

    for y in range(height):
        ty = y / float(height - 1)
        base = mix(top, bottom, ty)
        row = y * width * 3
        for x in range(width):
            tx = x / float(width - 1)
            r, g, b = base

            diag = math.sin((tx * 2.4 + ty * 1.6) * math.pi)
            if diag > 0.82:
                r, g, b = mix((r, g, b), accent, 0.28)

            column = min(columns - 1, int(tx * columns))
            if ty > 1.0 - skyline[column] * 0.42:
                r, g, b = mix((r, g, b), NAVY, 0.45)
                if abs(tx * columns - column - 0.5) > 0.46:
                    r, g, b = mix((r, g, b), (255, 255, 255), 0.08)

            dx = (tx - 0.72) * width
            dy = (ty - 0.3) * height
            dist = math.sqrt(dx * dx + dy * dy) / (width * 0.18)
            if 0.92 < dist < 1.0:
                r, g, b = mix((r, g, b), (255, 255, 255), 0.35)

            index = row + x * 3
            pixels[index] = r
            pixels[index + 1] = g
            pixels[index + 2] = b

    return pixels


def portrait(width, height, top, bottom, accent):
    pixels = bytearray(width * height * 3)
    head_x, head_y = 0.5, 0.38
    head_r = 0.17

    for y in range(height):
        ty = y / float(height - 1)
        base = mix(top, bottom, ty)
        row = y * width * 3
        for x in range(width):
            tx = x / float(width - 1)
            r, g, b = base

            r, g, b = mix((r, g, b), accent, max(0.0, 0.35 - (tx + ty) * 0.16))

            dx = (tx - head_x) * width
            dy = (ty - head_y) * height
            head = math.sqrt(dx * dx + dy * dy) <= head_r * height

            sx = (tx - 0.5) / 0.34
            sy = (ty - 1.05) / 0.42
            shoulders = ty > 0.6 and (sx * sx + sy * sy) <= 1.0

            if head or shoulders:
                r, g, b = mix((r, g, b), NAVY, 0.55)

            index = row + x * 3
            pixels[index] = r
            pixels[index + 1] = g
            pixels[index + 2] = b

    return pixels


def logo(width, height):
    pixels = bytearray(width * height * 4)
    cx, cy = height * 0.5, height * 0.5
    radius = height * 0.42

    for y in range(height):
        for x in range(width):
            index = (y * width + x) * 4
            dx, dy = x - cx, y - cy
            dist = math.sqrt(dx * dx + dy * dy)
            r, g, b, a = 0, 0, 0, 0

            if dist <= radius:
                r, g, b, a = BLUE[0], BLUE[1], BLUE[2], 255
                inner = (x - (cx - radius * 0.55)) / (radius * 1.1)
                if 0 <= inner <= 1:
                    column = inner * 3.0
                    if abs(column - round(column)) < 0.16 and cy - radius * 0.35 < y < cy + radius * 0.4:
                        r, g, b = 255, 255, 255
                if cy + radius * 0.42 < y < cy + radius * 0.58 and abs(dx) < radius * 0.62:
                    r, g, b = 255, 255, 255
                if cy - radius * 0.55 < y < cy - radius * 0.42 and abs(dx) < radius * 0.7:
                    r, g, b = 255, 255, 255

            bar_x = height * 1.15
            if x > bar_x:
                if height * 0.28 < y < height * 0.46 and x < bar_x + (width - bar_x) * 0.92:
                    r, g, b, a = NAVY[0], NAVY[1], NAVY[2], 255
                if height * 0.56 < y < height * 0.68 and x < bar_x + (width - bar_x) * 0.6:
                    r, g, b, a = BLUE_LIGHT[0], BLUE_LIGHT[1], BLUE_LIGHT[2], 255

            pixels[index] = r
            pixels[index + 1] = g
            pixels[index + 2] = b
            pixels[index + 3] = a

    return pixels


JOBS = [
    ('hero-1.png', 1920, 960, BLUE, NAVY, (140, 180, 255), 11),
    ('hero-2.png', 1920, 960, NAVY, TEAL, (120, 220, 210), 23),
    ('hero-3.png', 1920, 960, PLUM, NAVY, (220, 170, 255), 37),
    ('school-1.png', 1200, 800, BLUE, NAVY, (150, 190, 255), 101),
    ('school-2.png', 1200, 800, TEAL, NAVY, (130, 220, 205), 137),
    ('school-3.png', 1200, 800, GOLD, NAVY, (255, 215, 140), 149),
    ('program-1.png', 1200, 800, BLUE_LIGHT, NAVY, (160, 200, 255), 211),
    ('program-2.png', 1200, 800, PLUM, NAVY, (215, 175, 255), 233),
    ('program-3.png', 1200, 800, RED, NAVY, (255, 165, 175), 251),
    ('program-4.png', 1200, 800, TEAL, BLUE, (150, 230, 220), 271),
    ('announcement-1.png', 1200, 800, NAVY, BLUE, (170, 200, 255), 307),
    ('announcement-2.png', 1200, 800, NAVY, GOLD, (255, 220, 160), 331),
]


def main():
    for name, width, height, top, bottom, accent, seed in JOBS:
        path = os.path.join(HERE, name)
        size = write_png(path, width, height, scene(width, height, top, bottom, accent, seed))
        print('%-22s %5d x %-5d %6.1f KB' % (name, width, height, size / 1024.0))

    path = os.path.join(HERE, 'dean.png')
    size = write_png(path, 900, 1200, portrait(900, 1200, BLUE_LIGHT, NAVY, (190, 215, 255)))
    print('%-22s %5d x %-5d %6.1f KB' % ('dean.png', 900, 1200, size / 1024.0))

    path = os.path.join(HERE, 'logo.png')
    size = write_png(path, 520, 130, logo(520, 130), alpha=True)
    print('%-22s %5d x %-5d %6.1f KB' % ('logo.png', 520, 130, size / 1024.0))


if __name__ == '__main__':
    main()
