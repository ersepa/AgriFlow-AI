import * as THREE from "three";

const container = document.getElementById("weatherOrb");
const rain = Number(container.dataset.rain ?? 0);

const cloud = Number(container.dataset.cloud ?? 0);

const wind = Number(container.dataset.wind ?? 0);

const temp = Number(container.dataset.temp ?? 25);

/*
|--------------------------------------------------------------------------
| Jakarta Time
|--------------------------------------------------------------------------
*/

const hour = new Date().toLocaleString("en-US", {
    timeZone: "Asia/Jakarta",

    hour: "numeric",

    hour12: false,
});

const currentHour = Number(hour);

if (!container) {
    console.warn("Weather Orb container not found.");
} else {
    /*
    |--------------------------------------------------------------------------
    | Scene
    |--------------------------------------------------------------------------
    */

    const scene = new THREE.Scene();

    /*
    |--------------------------------------------------------------------------
    | Camera
    |--------------------------------------------------------------------------
    */

    const camera = new THREE.PerspectiveCamera(
        45,
        container.clientWidth / container.clientHeight,
        0.1,
        1000,
    );

    camera.position.set(0, 0, 4);

    /*
    |--------------------------------------------------------------------------
    | Renderer
    |--------------------------------------------------------------------------
    */

    const renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true,
    });

    renderer.setPixelRatio(window.devicePixelRatio);

    renderer.setSize(container.clientWidth, container.clientHeight);

    renderer.outputColorSpace = THREE.SRGBColorSpace;

    renderer.toneMapping = THREE.ACESFilmicToneMapping;

    renderer.toneMappingExposure = 1.15;

    renderer.shadowMap.enabled = true;

    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    container.appendChild(renderer.domElement);

    /*
    |--------------------------------------------------------------------------
    | Lights
    |--------------------------------------------------------------------------
    */

    const ambient = new THREE.AmbientLight(0xffffff, 1.2);

    scene.add(ambient);

    const directional = new THREE.DirectionalLight(
        0xffffff,

        4,
    );

    const angle = (currentHour / 24) * Math.PI * 2;

    directional.position.set(
        Math.cos(angle) * 6,

        2,

        Math.sin(angle) * 6,
    );

    /*
|--------------------------------------------------------------------------
| Day / Night
|--------------------------------------------------------------------------
*/

    const hour = new Date().getHours();

    const isNight = hour >= 18 || hour <= 5;

    scene.add(directional);

    const cyanLight = new THREE.PointLight(0x00e5ff, 18, 20);

    cyanLight.intensity = isNight ? 25 : 18;

    cyanLight.position.set(-3, 2, 4);

    scene.add(cyanLight);

    /*
|--------------------------------------------------------------------------
| Lightning Light
|--------------------------------------------------------------------------
*/

    const lightning = new THREE.PointLight(
        0xffffff,

        0,

        40,
    );

    lightning.position.set(0, 3, 2);

    scene.add(lightning);

    /*
    |--------------------------------------------------------------------------
    | Background Stars
    |--------------------------------------------------------------------------
    */

    const starsGeometry = new THREE.BufferGeometry();

    const stars = [];

    for (let i = 0; i < 1200; i++) {
        stars.push(
            (Math.random() - 0.5) * 120,
            (Math.random() - 0.5) * 120,
            (Math.random() - 0.5) * 120,
        );
    }

    starsGeometry.setAttribute(
        "position",

        new THREE.Float32BufferAttribute(stars, 3),
    );

    const starsMaterial = new THREE.PointsMaterial({
        color: 0xffffff,

        size: 0.08,

        transparent: true,

        opacity: Math.min(
            1,

            0.35 + rain * 0.08,
        ),
    });

    const starField = new THREE.Points(
        starsGeometry,

        starsMaterial,
    );

    scene.add(starField);

    /*
|--------------------------------------------------------------------------
| Wind Particles
|--------------------------------------------------------------------------
*/

    const windGeometry = new THREE.BufferGeometry();

    const windVertices = [];

    const windCount = 2500;

    for (let i = 0; i < windCount; i++) {
        const radius = 1.45 + Math.random() * 0.25;

        const theta = Math.random() * Math.PI * 2;

        const phi = Math.random() * Math.PI;

        windVertices.push(
            radius * Math.sin(phi) * Math.cos(theta),

            radius * Math.cos(phi),

            radius * Math.sin(phi) * Math.sin(theta),
        );
    }

    windGeometry.setAttribute(
        "position",

        new THREE.Float32BufferAttribute(
            windVertices,

            3,
        ),
    );

    const windMaterial = new THREE.PointsMaterial({
        color: 0x67e8f9,

        size: 0.012,

        transparent: true,

        opacity: 0.7,
    });

    const windParticles = new THREE.Points(
        windGeometry,

        windMaterial,
    );

    scene.add(windParticles);

    /*
|--------------------------------------------------------------------------
| Rain Particle System
|--------------------------------------------------------------------------
*/

    const rainGeometry = new THREE.BufferGeometry();

    const rainCount = Math.max(
        0,

        Math.min(
            12000,

            Math.round(rain * 1800),
        ),
    );

    const rainVertices = [];

    for (let i = 0; i < rainCount; i++) {
        rainVertices.push(
            (Math.random() - 0.5) * 20,

            Math.random() * 15,

            (Math.random() - 0.5) * 20,
        );
    }

    rainGeometry.setAttribute(
        "position",

        new THREE.Float32BufferAttribute(
            rainVertices,

            3,
        ),
    );

    const rainMaterial = new THREE.PointsMaterial({
        color: 0x7dd3fc,

        size: 0.03,

        transparent: true,

        opacity: 0.75,
    });

    const rainParticle = new THREE.Points(
        rainGeometry,

        rainMaterial,
    );

    scene.add(rainParticle);

    // Tampilkan hanya jika hujan
    rainParticle.visible = (rain ?? 0) > 0;

    /*
|--------------------------------------------------------------------------
| Earth Texture
|--------------------------------------------------------------------------
*/

    const loader = new THREE.TextureLoader();

    const earthTexture = loader.load("/textures/earth_day.jpg");

    const nightTexture = loader.load("/textures/earth_night.jpg");

    const cloudTexture = loader.load("/textures/earth_clouds.jpg");

    const bumpTexture = loader.load("/textures/earth_bump.jpg");

    const specularTexture = loader.load("/textures/earth_specular.jpg");

    const earthMaterial = new THREE.MeshStandardMaterial({
        map: earthTexture,

        bumpMap: bumpTexture,

        bumpScale: 0.05,

        roughnessMap: specularTexture,

        roughness: 0.85,

        metalness: 0.02,

        emissiveMap: nightTexture,

        emissive: new THREE.Color(0xffffff),

        emissiveIntensity: 0,
    });

    const sphere = new THREE.Mesh(
        new THREE.SphereGeometry(1, 256, 256),

        earthMaterial,
    );

    scene.add(sphere);

    if (isNight) {
        earthMaterial.emissive = new THREE.Color(0x111111);
    }

    /*
|--------------------------------------------------------------------------
| Weather Color
|--------------------------------------------------------------------------
*/

    if (rain > 2) {
        earthMaterial.color.set(0x6ea8ff);
    } else if (cloud > 60) {
        earthMaterial.color.set(0x9db3c7);
    } else {
        earthMaterial.color.set(0xffffff);
    }

    /*
|--------------------------------------------------------------------------
| Atmosphere
|--------------------------------------------------------------------------
*/

    const atmosphere = new THREE.Mesh(
        new THREE.SphereGeometry(1.05, 128, 128),

        new THREE.MeshBasicMaterial({
            color: isNight ? 0x1d4ed8 : 0x66e3ff,

            transparent: true,

            opacity: 0.18,

            side: THREE.BackSide,
        }),
    );

    scene.add(atmosphere);

    const clouds = new THREE.Mesh(
        new THREE.SphereGeometry(1.012, 128, 128),

        new THREE.MeshStandardMaterial({
            map: cloudTexture,

            transparent: true,

            opacity: 0.42,

            depthWrite: false,
        }),
    );

    scene.add(clouds);

    /*
    |--------------------------------------------------------------------------
    | Glow Sphere
    |--------------------------------------------------------------------------
    */

    const glow = new THREE.Mesh(
        new THREE.SphereGeometry(1.18, 128, 128),

        new THREE.MeshBasicMaterial({
            color: isNight ? 0x2563eb : 0x00d9ff,

            transparent: true,

            opacity: isNight ? 0.13 : 0.08,

            side: THREE.BackSide,
        }),
    );

    scene.add(glow);

    /*
|--------------------------------------------------------------------------
| Dynamic Glow
|--------------------------------------------------------------------------
*/

    const glowTargetColor = new THREE.Color();

    if (isNight) {
        glowTargetColor.set(0x2563eb);
    } else if (rain > 3) {
        glowTargetColor.set(0x6ea8ff);
    } else if (cloud > 80) {
        glowTargetColor.set(0x94a3b8);
    } else if (temp > 33) {
        glowTargetColor.set(0xffb347);
    } else {
        glowTargetColor.set(0x00d9ff);
    }

    glow.material.color.lerp(glowTargetColor, 0.03);

    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

    function resize() {
        camera.aspect = container.clientWidth / container.clientHeight;

        camera.updateProjectionMatrix();

        renderer.setSize(
            container.clientWidth,

            container.clientHeight,
        );
    }

    window.addEventListener(
        "resize",

        resize,
    );

    /*
    |--------------------------------------------------------------------------
    | Animation
    |--------------------------------------------------------------------------
    */

    const clock = new THREE.Clock();
    /*
|--------------------------------------------------------------------------
| Lightning State
|--------------------------------------------------------------------------
*/

    let flash = 0;

    let nextFlash = Math.random() * 6 + 2;

    function animate() {
        requestAnimationFrame(animate);

        const t = clock.getElapsedTime();

        /*
|--------------------------------------------------------------------------
| Camera Floating
|--------------------------------------------------------------------------
*/

        camera.position.x = Math.sin(t * 0.25) * 0.08;

        camera.position.y = Math.cos(t * 0.22) * 0.05;

        camera.lookAt(0, 0, 0);

        /*
|--------------------------------------------------------------------------
| Dynamic Atmosphere Color
|--------------------------------------------------------------------------
*/

        const targetColor = new THREE.Color();

        if (rain > 3) {
            targetColor.set(0x4f7cff);
        } else if (cloud > 80) {
            targetColor.set(0x94a3b8);
        } else if (temp > 33) {
            targetColor.set(0xffb347);
        } else {
            targetColor.set(0x66e3ff);
        }

        atmosphere.material.color.lerp(targetColor, 0.02);

        /*
|--------------------------------------------------------------------------
| Earth Day / Night Transition
|--------------------------------------------------------------------------
*/

        const targetEmission = isNight ? 1.2 : 0;

        earthMaterial.emissiveIntensity +=
            (targetEmission - earthMaterial.emissiveIntensity) * 0.04;

        directional.position.x = Math.cos(t * 0.02 + angle) * 6;

        directional.position.z = Math.sin(t * 0.02 + angle) * 6;

        sphere.rotation.y += 0.0012;

        sphere.scale.setScalar(1 + Math.sin(t * 1.5) * 0.005);

        clouds.rotation.y += 0.0026;

        atmosphere.rotation.y += 0.0005;

        atmosphere.scale.setScalar(1.05 + Math.sin(t * 2) * 0.004);

        sphere.rotation.x = Math.sin(t * 0.5) * 0.08;

        glow.rotation.y += 0.0015;

        glow.rotation.x += 0.0002;

        glow.rotation.z += 0.0001;

        const pulse = 1 + Math.sin(t * 2) * 0.02 + rain * 0.0015;

        glow.scale.setScalar(pulse);

        if (flash > 0) {
            earthMaterial.emissiveIntensity += flash * 0.25;
            glow.material.opacity = 0.15 + flash * 0.25;
        } else {
            glow.material.opacity = rain > 2 ? 0.18 : 0.09;
        }

        clouds.material.opacity = 0.35 + (cloud / 100) * 0.45 + flash * 0.15;

        starField.rotation.y += 0.00015;

        windParticles.rotation.y += 0.0005 + wind * 0.00018;

        windParticles.rotation.x += 0.0001;

        cyanLight.position.x = Math.sin(t) * 4;

        cyanLight.position.z = Math.cos(t) * 4;

        /*
|--------------------------------------------------------------------------
| Rain Animation
|--------------------------------------------------------------------------
*/

        if (rainParticle.visible) {
            const positions = rainGeometry.attributes.position.array;

            for (let i = 1; i < positions.length; i += 3) {
                positions[i] -= 0.015 + rain * 0.01;

                if (positions[i] < -5) {
                    positions[i] = 10;
                }
            }

            rainGeometry.attributes.position.needsUpdate = true;
        }

        /*
|--------------------------------------------------------------------------
| Lightning Engine
|--------------------------------------------------------------------------
*/

        if (rain > 3 || cloud > 80) {
            nextFlash -= 0.016;

            if (nextFlash <= 0) {
                flash = 1;

                nextFlash = Math.random() * 6 + 2;
            }
        }

        if (flash > 0) {
            lightning.intensity = 25 * flash;
            earthMaterial.emissiveIntensity += flash * 0.35;

            ambient.intensity = 1.2 + flash * 2;

            flash -= 0.12;
        } else {
            lightning.intensity = 0;

            ambient.intensity = isNight ? 0.35 : 1.2;
        }

        /*
|--------------------------------------------------------------------------
| Day Night Transition
|--------------------------------------------------------------------------
*/

        renderer.render(
            scene,

            camera,
        );
    }

    animate();
}
