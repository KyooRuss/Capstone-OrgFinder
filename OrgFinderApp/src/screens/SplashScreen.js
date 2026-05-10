import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Animated, Image } from 'react-native';

export default function SplashScreen() {
    const scale = useRef(new Animated.Value(0.7)).current;
    const opacity = useRef(new Animated.Value(0)).current;

    useEffect(() => {
        Animated.parallel([
            Animated.spring(scale, { toValue: 1, useNativeDriver: true }),
            Animated.timing(opacity, { toValue: 1, duration: 600, useNativeDriver: true }),
        ]).start();
    }, []);

    return (
        <View style={styles.container}>
            <Animated.View style={{ transform: [{ scale }], opacity }}>
                <Image
                    source={require('../../assets/orgfinder-logo.png')}
                    style={styles.logo}
                    resizeMode="contain"
                />
                <Text style={styles.title}>ORGFINDER</Text>
            </Animated.View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#4A6CF7',
        alignItems: 'center',
        justifyContent: 'center',
    },
    logo: {
        width: 100,
        height: 100,
        alignSelf: 'center',
        marginBottom: 16,
    },
    title: {
        color: '#fff',
        fontSize: 28,
        fontWeight: '800',
        letterSpacing: 4,
        textAlign: 'center',
    },
});
