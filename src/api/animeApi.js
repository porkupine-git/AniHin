// API and Service layer for Anime Data queries

import { animeDatabase } from '../data/animeDatabase.js';

export const animeApi = {
    // 1. Get Spotlight Anime (Hero Slider)
    getSpotlightAnime() {
        return animeDatabase
            .filter(a => a.spotlightRank)
            .sort((a, b) => a.spotlightRank - b.spotlightRank);
    },

    // 2. Get Trending Anime
    getTrendingAnime() {
        return [
            animeDatabase.find(a => a.slug === 'one-piece'),
            animeDatabase.find(a => a.slug === 'bleach-thousand-year-blood-war'),
            animeDatabase.find(a => a.slug === 'attack-on-titan'),
            animeDatabase.find(a => a.slug === 'demon-slayer-kimetsu-no-yaiba'),
            animeDatabase.find(a => a.slug === 'cyberpunk-edgerunners-2'),
            animeDatabase.find(a => a.slug === 'black-clover-season-2'),
            animeDatabase.find(a => a.slug === 'one-punch-man'),
            animeDatabase.find(a => a.slug === 'death-note')
        ].filter(Boolean);
    },

    // 3. Get 4 Featured Columns (Top Airing, Most Popular, Most Favorite, Latest Completed)
    getFeaturedColumns() {
        return {
            topAiring: [
                animeDatabase.find(a => a.slug === 'one-piece'),
                animeDatabase.find(a => a.slug === 'witch-hat-atelier'),
                animeDatabase.find(a => a.slug === 'kaiju-no-8-season-2'),
                animeDatabase.find(a => a.slug === 'case-closed'),
                animeDatabase.find(a => a.slug === 'rezero-starting-life-in-another-world-season-4')
            ].filter(Boolean),
            mostPopular: [
                animeDatabase.find(a => a.slug === 'attack-on-titan'),
                animeDatabase.find(a => a.slug === 'death-note'),
                animeDatabase.find(a => a.slug === 'fullmetal-alchemist-brotherhood'),
                animeDatabase.find(a => a.slug === 'one-punch-man'),
                animeDatabase.find(a => a.slug === 'demon-slayer-kimetsu-no-yaiba')
            ].filter(Boolean),
            mostFavorite: [
                animeDatabase.find(a => a.slug === 'cyberpunk-edgerunners-2'),
                animeDatabase.find(a => a.slug === 'case-closed'),
                animeDatabase.find(a => a.slug === 'reborn-as-a-goblin'),
                animeDatabase.find(a => a.slug === 'tensei-shitara-ken-deshita-2nd'),
                animeDatabase.find(a => a.slug === 'black-clover-season-2')
            ].filter(Boolean),
            latestCompleted: [
                animeDatabase.find(a => a.slug === 'cyberpunk-edgerunners-2'),
                animeDatabase.find(a => a.slug === 'death-note'),
                animeDatabase.find(a => a.slug === 'attack-on-titan'),
                animeDatabase.find(a => a.slug === 'fullmetal-alchemist-brotherhood'),
                animeDatabase.find(a => a.slug === 'one-punch-man')
            ].filter(Boolean)
        };
    },

    // 4. Get Latest Episodes Grid
    getLatestEpisodes() {
        return animeDatabase;
    },

    // 5. Get Top 10 Widget List
    getTop10(timeframe = 'today') {
        if (timeframe === 'month') {
            return [
                animeDatabase.find(a => a.slug === 'attack-on-titan'),
                animeDatabase.find(a => a.slug === 'death-note'),
                animeDatabase.find(a => a.slug === 'one-piece'),
                animeDatabase.find(a => a.slug === 'fullmetal-alchemist-brotherhood'),
                animeDatabase.find(a => a.slug === 'demon-slayer-kimetsu-no-yaiba'),
                animeDatabase.find(a => a.slug === 'one-punch-man')
            ].filter(Boolean);
        } else if (timeframe === 'week') {
            return [
                animeDatabase.find(a => a.slug === 'bleach-thousand-year-blood-war'),
                animeDatabase.find(a => a.slug === 'one-piece'),
                animeDatabase.find(a => a.slug === 'black-clover-season-2'),
                animeDatabase.find(a => a.slug === 'cyberpunk-edgerunners-2'),
                animeDatabase.find(a => a.slug === 'kaiju-no-8-season-2'),
                animeDatabase.find(a => a.slug === 'rezero-starting-life-in-another-world-season-4')
            ].filter(Boolean);
        }
        // default: today
        return [
            animeDatabase.find(a => a.slug === 'one-piece'),
            animeDatabase.find(a => a.slug === 'bleach-thousand-year-blood-war'),
            animeDatabase.find(a => a.slug === 'black-clover-season-2'),
            animeDatabase.find(a => a.slug === 'cyberpunk-edgerunners-2'),
            animeDatabase.find(a => a.slug === 'reborn-as-a-goblin'),
            animeDatabase.find(a => a.slug === 'witch-hat-atelier')
        ].filter(Boolean);
    },

    // 6. Find anime by Slug or ID
    getAnimeBySlug(slug) {
        if (!slug) return animeDatabase[0];
        // clean any query string
        const cleanSlug = slug.split('?')[0].toLowerCase();
        return animeDatabase.find(a => a.slug === cleanSlug || cleanSlug.includes(a.slug) || a.slug.includes(cleanSlug)) || animeDatabase[0];
    },

    getAnimeById(id) {
        return animeDatabase.find(a => a.id === String(id)) || animeDatabase[0];
    },

    // 7. Search anime
    search(query) {
        if (!query || !query.trim()) return [];
        const q = query.trim().toLowerCase();
        return animeDatabase.filter(a =>
            a.title.toLowerCase().includes(q) ||
            a.jname.toLowerCase().includes(q) ||
            a.genres.some(g => g.toLowerCase().includes(q))
        );
    },

    // 8. Filter anime
    filter(criteria = {}) {
        return animeDatabase.filter(anime => {
            if (criteria.type && criteria.type !== 'all' && anime.type.toLowerCase() !== criteria.type.toLowerCase()) {
                return false;
            }
            if (criteria.status && criteria.status !== 'all' && !anime.status.toLowerCase().includes(criteria.status.toLowerCase())) {
                return false;
            }
            if (criteria.genre && criteria.genre.length > 0) {
                const hasGenre = criteria.genre.every(g =>
                    anime.genres.map(x => x.toLowerCase()).includes(g.toLowerCase())
                );
                if (!hasGenre) return false;
            }
            if (criteria.language && criteria.language === 'dub' && (!anime.dubCount || anime.dubCount <= 0)) {
                return false;
            }
            return true;
        });
    }
};
